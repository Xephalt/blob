#!/usr/bin/env bash
# ============================================================
# wp-link-replacer.sh
# Détecte les liens redirigés dans WordPress (via Docker)
# et les remplace par leur destination finale avec WP-CLI.
#
# Usage : ./wp-link-replacer.sh [--config path/to/config.env]
# ============================================================

set -euo pipefail

# ─── Couleurs ────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
RESET='\033[0m'

# ─── Config par défaut ───────────────────────────────────────
CONFIG_FILE="./config.env"

# ─── Arguments ───────────────────────────────────────────────
while [[ $# -gt 0 ]]; do
  case "$1" in
    --config)
      CONFIG_FILE="$2"
      shift 2
      ;;
    *)
      echo -e "${RED}Argument inconnu : $1${RESET}"
      exit 1
      ;;
  esac
done

# ─── Chargement de la config ─────────────────────────────────
if [[ ! -f "$CONFIG_FILE" ]]; then
  echo -e "${RED}Fichier de config introuvable : $CONFIG_FILE${RESET}"
  exit 1
fi

# shellcheck disable=SC1090
source "$CONFIG_FILE"

# ─── Validation des variables requises ───────────────────────
REQUIRED_VARS=(WP_CONTAINER DB_CONTAINER WP_PATH DB_HOST DB_NAME DB_USER DB_PASS DB_PREFIX CURL_TIMEOUT CURL_MAX_REDIRS DO_REPLACE OUTPUT_DIR)
for var in "${REQUIRED_VARS[@]}"; do
  if [[ -z "${!var:-}" ]]; then
    echo -e "${RED}Variable manquante dans la config : $var${RESET}"
    exit 1
  fi
done

# ─── Init dossier output ─────────────────────────────────────
mkdir -p "$OUTPUT_DIR"
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
OLD_LINKS_FILE="$OUTPUT_DIR/old_links_${TIMESTAMP}.txt"
NEW_LINKS_FILE="$OUTPUT_DIR/new_links_${TIMESTAMP}.txt"
REPORT_FILE="$OUTPUT_DIR/report_${TIMESTAMP}.log"
SKIPPED_FILE="$OUTPUT_DIR/skipped_${TIMESTAMP}.log"

# ─── Fonctions utilitaires ───────────────────────────────────
log()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
warn()   { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
success(){ echo -e "${GREEN}[OK]${RESET}    $*"; }
error()  { echo -e "${RED}[ERROR]${RESET} $*"; }
section(){ echo -e "\n${BOLD}${BLUE}══════════════════════════════════════${RESET}"; echo -e "${BOLD}${BLUE}  $*${RESET}"; echo -e "${BOLD}${BLUE}══════════════════════════════════════${RESET}"; }

# Vérifie qu'un container Docker tourne
check_container() {
  local name="$1"
  if ! docker ps --format '{{.Names}}' | grep -q "^${name}$"; then
    error "Container '$name' introuvable ou non démarré."
    error "Lance 'docker ps' pour vérifier les noms exacts."
    exit 1
  fi
}

# Exécute une requête SQL dans le container DB
run_sql() {
  docker exec "$DB_CONTAINER" \
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    --skip-column-names --silent \
    -e "$1" 2>/dev/null
}

# Suit les redirections et retourne l'URL finale
resolve_url() {
  local url="$1"
  curl \
    --silent \
    --location \
    --max-redirs "$CURL_MAX_REDIRS" \
    --connect-timeout "$CURL_TIMEOUT" \
    --max-time "$CURL_TIMEOUT" \
    --output /dev/null \
    --write-out "%{url_effective}" \
    "$url" 2>/dev/null || echo "$url"
}

# ─── Début du script ─────────────────────────────────────────
section "WordPress Link Replacer — $TIMESTAMP"
log "Config     : $CONFIG_FILE"
log "Container  : $WP_CONTAINER (WP) / $DB_CONTAINER (DB)"
log "Base       : $DB_NAME"
log "DO_REPLACE : $DO_REPLACE"
log "Output     : $OUTPUT_DIR"
echo ""

# ─── Vérification des containers ─────────────────────────────
section "1. Vérification des containers Docker"
check_container "$WP_CONTAINER"
success "Container WP     '$WP_CONTAINER' actif"
check_container "$DB_CONTAINER"
success "Container DB     '$DB_CONTAINER' actif"

# ─── Extraction des URLs depuis la DB ────────────────────────
section "2. Extraction des URLs depuis la base de données"

log "Extraction depuis ${DB_PREFIX}posts (post_content)..."
LINKS_FROM_POSTS=$(run_sql "
  SELECT post_content FROM ${DB_PREFIX}posts
  WHERE post_status IN ('publish', 'draft', 'private')
    AND post_content LIKE '%http%';
" | grep -oE 'https?://[^\"<> )]+' | sort -u || true)

log "Extraction depuis ${DB_PREFIX}postmeta (meta_value)..."
LINKS_FROM_META=$(run_sql "
  SELECT meta_value FROM ${DB_PREFIX}postmeta
  WHERE meta_value LIKE '%http%';
" | grep -oE 'https?://[^\"<> )]+' | sort -u || true)

log "Extraction depuis ${DB_PREFIX}options (option_value)..."
LINKS_FROM_OPTIONS=$(run_sql "
  SELECT option_value FROM ${DB_PREFIX}options
  WHERE option_value LIKE '%http%'
    AND option_name NOT IN ('cron', '_transient%', '_site_transient%');
" | grep -oE 'https?://[^\"<> )]+' | sort -u || true)

# Fusion et dédoublonnage
ALL_LINKS=$(echo -e "${LINKS_FROM_POSTS}\n${LINKS_FROM_META}\n${LINKS_FROM_OPTIONS}" \
  | grep -E '^https?://' \
  | sort -u \
  | grep -v '^$' || true)

TOTAL=$(echo "$ALL_LINKS" | grep -c '.' || echo 0)
success "Total liens uniques trouvés : $TOTAL"

if [[ "$TOTAL" -eq 0 ]]; then
  warn "Aucun lien trouvé. Vérifie le préfixe de table (DB_PREFIX=$DB_PREFIX) et la connexion DB."
  exit 0
fi

# ─── Détection des redirections ──────────────────────────────
section "3. Détection des redirections (curl -L)"
log "Résolution de chaque lien... (peut prendre du temps selon $TOTAL URLs)"
echo ""

REDIRECT_COUNT=0
CHECKED=0

# Vide les fichiers de sortie
> "$OLD_LINKS_FILE"
> "$NEW_LINKS_FILE"
> "$SKIPPED_FILE"

while IFS= read -r url; do
  [[ -z "$url" ]] && continue
  CHECKED=$((CHECKED + 1))

  printf "\r  [%d/%d] %-80s" "$CHECKED" "$TOTAL" "${url:0:78}"

  RESOLVED=$(resolve_url "$url")

  # Normalise (supprime trailing slash pour comparaison)
  URL_NORM="${url%/}"
  RESOLVED_NORM="${RESOLVED%/}"

  if [[ "$URL_NORM" != "$RESOLVED_NORM" && -n "$RESOLVED" && "$RESOLVED" != "$url" ]]; then
    echo "$url"       >> "$OLD_LINKS_FILE"
    echo "$RESOLVED"  >> "$NEW_LINKS_FILE"
    REDIRECT_COUNT=$((REDIRECT_COUNT + 1))
  fi

done <<< "$ALL_LINKS"

echo ""
echo ""
success "Redirections détectées : $REDIRECT_COUNT / $TOTAL"

if [[ "$REDIRECT_COUNT" -eq 0 ]]; then
  warn "Aucune redirection détectée. Tous les liens pointent déjà vers leur destination."
  echo -e "${GREEN}Rien à remplacer. Bonne nouvelle !${RESET}"
  exit 0
fi

# ─── Affichage du mapping ────────────────────────────────────
section "4. Mapping détecté"
echo "" | tee -a "$REPORT_FILE"
echo "════════════════════════════════════════════════════════════" | tee -a "$REPORT_FILE"
echo "  RAPPORT — $TIMESTAMP" | tee -a "$REPORT_FILE"
echo "  Site : ${DB_NAME} | Container : ${WP_CONTAINER}" | tee -a "$REPORT_FILE"
echo "════════════════════════════════════════════════════════════" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

paste "$OLD_LINKS_FILE" "$NEW_LINKS_FILE" | while IFS=$'\t' read -r old new; do
  echo "  OLD: $old" | tee -a "$REPORT_FILE"
  echo "  NEW: $new" | tee -a "$REPORT_FILE"
  echo "  ──────────────────────────────────────────────────────" | tee -a "$REPORT_FILE"
done

echo "" | tee -a "$REPORT_FILE"

# ─── Remplacement via WP-CLI ─────────────────────────────────
section "5. Remplacement WP-CLI"

if [[ "$DO_REPLACE" != "true" ]]; then
  warn "DO_REPLACE=false → mode dry-run, aucun remplacement effectué."
  warn "Mets DO_REPLACE=true dans la config pour appliquer les changements."
  echo ""
  log "Fichiers générés :"
  log "  Anciens liens : $OLD_LINKS_FILE"
  log "  Nouveaux liens: $NEW_LINKS_FILE"
  log "  Rapport       : $REPORT_FILE"
  exit 0
fi

REPLACED=0
FAILED=0

paste "$OLD_LINKS_FILE" "$NEW_LINKS_FILE" | while IFS=$'\t' read -r old new; do
  [[ -z "$old" || -z "$new" ]] && continue

  log "Remplacement : $old → $new"

  if docker exec "$WP_CONTAINER" \
    wp search-replace "$old" "$new" \
    --path="$WP_PATH" \
    --all-tables \
    --precise \
    --skip-columns=guid \
    2>&1 | tee -a "$REPORT_FILE"; then

    echo "  [REPLACED] $old -> $new" >> "$REPORT_FILE"
    REPLACED=$((REPLACED + 1))
    success "OK : $old"
  else
    echo "  [FAILED]   $old -> $new" >> "$REPORT_FILE"
    FAILED=$((FAILED + 1))
    error "FAIL : $old"
  fi
done

# ─── Résumé final ────────────────────────────────────────────
section "6. Résumé"

{
  echo ""
  echo "════════════════════════════════════════════════════════════"
  echo "  RÉSUMÉ FINAL"
  echo "════════════════════════════════════════════════════════════"
  echo "  Liens scannés    : $TOTAL"
  echo "  Redirections     : $REDIRECT_COUNT"
  echo "  Remplacés        : $REPLACED"
  echo "  Échecs           : $FAILED"
  echo "  Rapport complet  : $REPORT_FILE"
  echo "════════════════════════════════════════════════════════════"
} | tee -a "$REPORT_FILE"

echo ""
success "Script terminé. Rapport : $REPORT_FILE"
