# Mac : les commandes, dans l'ordre

## Juste Oh My Posh

Prérequis : le repo récupéré dans `~/blob` (voir le [README principal](../README.md)).

Installe Oh My Posh :
```bash
brew install oh-my-posh
```
Installe la police du PC :
```bash
brew install --cask font-caskaydia-cove-nerd-font
```
Pose le thème (et la config Ghostty, l'ancienne est sauvegardée) :
```bash
cd ~/blob/mac && make prompt
```
Active-le :
```bash
echo 'eval "$(oh-my-posh init zsh --config ~/.config/oh-my-posh/tokyonight_stormx.omp.json)"' >> ~/.zshrc && exec zsh
```

Dans l'app Terminal (pas Ghostty), choisis la police à la main : Terminal > Réglages > Profils > Police > CaskaydiaCove Nerd Font.

---

## Suggestions grises et rappels d'alias

Installe les plugins :
```bash
brew install zsh-autosuggestions zsh-syntax-highlighting zsh-you-should-use
```
Active-les (colle le bloc entier) :
```bash
cat >> ~/.zshrc <<'EOF'
zstyle ':completion:*' menu select
source "$(brew --prefix)/share/zsh-autosuggestions/zsh-autosuggestions.zsh"
source "$(brew --prefix)/share/zsh-you-should-use/you-should-use.plugin.zsh"
source "$(brew --prefix)/share/zsh-syntax-highlighting/zsh-syntax-highlighting.zsh"
EOF
exec zsh
```

Flèche droite accepte la suggestion grise. Tab ouvre la liste navigable.

---

## Tout d'un coup

Mets d'abord tes `.pem` dans `~/Workspace/certificates` (étape 4), puis :

```bash
git clone https://github.com/Xephalt/blob.git ~/blob
cd ~/blob/mac
make all GIT_NAME="Prénom Nom" GIT_EMAIL="prenom.nom@entreprise.com"
```

Tu peux aussi lancer une étape seule : `make` les liste (`make brew`, `make dotfiles`, `make prompt`, `make java`, `make node`, `make ssh`, `make git`, `make doctor`). Tout est relançable. `make doctor` dit ce qui manque.

Ton ancien `~/.zshrc` est gardé : copie en `.zshrc.bak.<date>`, et ses lignes passent dans `~/.zshrc.local`.

---

## Ou à la main

Une commande à la fois. Pour recharger le Terminal sans le fermer : `exec zsh`. Après chaque bloc, regarde la ligne **Résultat attendu**. Si ce n'est pas ça, stop et envoie une photo.

---

## 1. Homebrew marche ?

```bash
brew --version
```
**Résultat attendu** : une ligne `Homebrew` suivie d'un numéro de version.
Sinon : installe Homebrew depuis l'app **Self Service**, puis :
```bash
nano ~/.zshrc
```
colle la ligne `export PATH=...` donnée par Self Service, `Ctrl+O`, `Entrée`, `Ctrl+X`, puis :
```bash
source ~/.zshrc
brew --version
```

---

## 2. Vérifier le .zshrc

```bash
cat ~/.zshrc
```
**Résultat attendu** : seulement la ou les lignes Homebrew.
S'il y a des lignes `JAVA_HOME` ou `PATH=...openjdk...` ou `node@24` (d'une ancienne version de ce guide) : `nano ~/.zshrc`, supprime-les, `Ctrl+O`, `Entrée`, `Ctrl+X`.

---

## 3. Installer les outils

```bash
brew install openjdk@21 jenv maven python angular-cli
```
**Résultat attendu** : pas de ligne `Error`. (C'est long, c'est normal.)

---

## 4. Certificats de l'entreprise

1. Récupère les certificats `.pem` de ton entreprise (proxy / inspection TLS), auprès de ton IT.
2. Mets-les dans le bon dossier :
```bash
mkdir -p ~/Workspace/certificates
mv ~/Downloads/*.pem ~/Workspace/certificates/
ls ~/Workspace/certificates
```
**Résultat attendu** : la liste des fichiers `.pem` (au moins 2).

3. Les importer dans Java 21 :
```bash
JVM="$(brew --prefix openjdk@21)/libexec/openjdk.jdk/Contents/Home"
cd ~/Workspace/certificates
for f in *.pem; do "$JVM/bin/keytool" -importcert -noprompt -alias "${f%.*}" -file "$f" -keystore "$JVM/lib/security/cacerts" -storepass changeit; done
cd ~
```
**Résultat attendu** : `Certificate was added to keystore` une fois par fichier.

---

## 5. jenv (choisit la version de Java)

```bash
echo 'eval "$(jenv init -)"' >> ~/.zshrc
source ~/.zshrc
jenv enable-plugin export
```
Recharge le shell, puis déclare le JDK :
```bash
exec zsh
```
```bash
jenv add "$(brew --prefix openjdk@21)/libexec/openjdk.jdk/Contents/Home"
jenv global 21.0
```

---

## 6. Vérification finale

```bash
exec zsh
```
```bash
java -version
echo $JAVA_HOME
mvn -v
ng version
python3 --version
```
**Résultat attendu** :
- `java -version` → `21.x`
- `echo $JAVA_HOME` → un chemin qui contient `.jenv`
- `mvn -v` → une ligne `Java version: 21...`
- `ng version` → `Angular CLI: ...`
- `python3 --version` → `Python 3.x`

---

## Plus tard (pas maintenant)
- Registre Maven interne + proxy de l'entreprise : à mettre dans `~/.m2/settings.xml` et `~/.zshrc.local`, jamais dans ce repo.
- Git + clé SSH GitLab, Docker Desktop, VS Code.

⚠️ Après un `brew upgrade openjdk@21`, refais l'étape 4.3 (les certificats sautent avec la mise à jour).
