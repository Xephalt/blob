#!/usr/bin/env bash
# ============================================================
# install-wpcli.sh
# Installe WP-CLI dans un container WordPress Docker.
# Idempotent : ne réinstalle pas si déjà présent.
#
# Usage : ./install-wpcli.sh [--config path/to/config.env]
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

log()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
success(){ echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()   { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()  { echo -e "${RED}[ERROR]${RESET} $*"; }
section(){ echo -e "\n${BOLD}${BLUE}══════════════════════════════════════${RESET}"; echo -e "${BOLD}${BLUE}  $*${RESET}"; echo -e "${BOLD}${BLUE}══════════════════════════════════════${RESET}"; }

# ─── Config ──────────────────────────────────────────────────
CONFIG_FILE="./config.env"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --config) CONFIG_FILE="$2"; shift 2 ;;
    *) error "Argument inconnu : $1"; exit 1 ;;
  esac
done

if [[ ! -f "$CONFIG_FILE" ]]; then
  error "Fichier de config introuvable : $CONFIG_FILE"
  exit 1
fi

# shellcheck disable=SC1090
source "$CONFIG_FILE"

# ─── Constantes WP-CLI ───────────────────────────────────────
WPCLI_VERSION="2.10.0"
WPCLI_URL="https://github.com/wp-cli/wp-cli/releases/download/v${WPCLI_VERSION}/wp-cli-${WPCLI_VERSION}.phar"
WPCLI_DEST="/usr/local/bin/wp"

# ─── Vérification container ──────────────────────────────────
section "1. Vérification du container"

if ! docker ps --format '{{.Names}}' | grep -q "^${WP_CONTAINER}$"; then
  error "Container '$WP_CONTAINER' introuvable ou non démarré."
  error "Lance 'docker ps' pour vérifier les noms."
  exit 1
fi
success "Container '$WP_CONTAINER' actif"

# ─── Idempotence : déjà installé ? ───────────────────────────
section "2. Vérification WP-CLI existant"

if docker exec "$WP_CONTAINER" which wp &>/dev/null; then
  EXISTING_VERSION=$(docker exec "$WP_CONTAINER" wp --version --allow-root 2>/dev/null || echo "inconnue")
  warn "WP-CLI déjà présent dans le container : $EXISTING_VERSION"
  warn "Rien à faire. Supprime $WPCLI_DEST dans le container pour forcer la réinstallation."
  exit 0
fi

log "WP-CLI absent → installation en cours..."

# ─── Détection du package manager dispo ──────────────────────
section "3. Installation des dépendances (curl)"

# Les images WordPress officielles sont Debian-based
if docker exec "$WP_CONTAINER" which curl &>/dev/null; then
  log "curl déjà disponible dans le container"
elif docker exec "$WP_CONTAINER" which apt-get &>/dev/null; then
  log "Installation de curl via apt-get..."
  docker exec "$WP_CONTAINER" bash -c "apt-get update -qq && apt-get install -y -qq curl" \
    && success "curl installé"
elif docker exec "$WP_CONTAINER" which apk &>/dev/null; then
  log "Installation de curl via apk (Alpine)..."
  docker exec "$WP_CONTAINER" apk add --no-cache curl \
    && success "curl installé"
else
  error "Impossible d'installer curl : ni apt-get ni apk trouvés dans le container."
  error "Installe curl manuellement dans ton image Docker."
  exit 1
fi

# ─── Téléchargement et installation ──────────────────────────
section "4. Téléchargement de WP-CLI v${WPCLI_VERSION}"

log "Source : $WPCLI_URL"
log "Dest   : $WPCLI_DEST"

docker exec "$WP_CONTAINER" bash -c "
  curl -fsSL '${WPCLI_URL}' -o '${WPCLI_DEST}' \
    && chmod +x '${WPCLI_DEST}'
" && success "WP-CLI téléchargé et rendu exécutable"

# ─── Vérification de l'installation ──────────────────────────
section "5. Vérification"

INSTALLED_VERSION=$(docker exec "$WP_CONTAINER" wp --version --allow-root 2>/dev/null || true)

if [[ -z "$INSTALLED_VERSION" ]]; then
  error "WP-CLI installé mais la commande 'wp --version' échoue."
  error "Vérifie que php est disponible dans le container."
  exit 1
fi

success "WP-CLI installé avec succès : $INSTALLED_VERSION"
log "Tu peux maintenant lancer : ./wp-link-replacer.sh --config $CONFIG_FILE"
