#!/usr/bin/env bash
set -u
SRC="${SRC:-/src}"
pass=0; fail=0

ok()  { pass=$((pass + 1)); echo "ok   - $1"; }
nok() { fail=$((fail + 1)); echo "FAIL - $1"; }
check() { if eval "$2"; then ok "$1"; else nok "$1"; fi; }

stub() {
  printf '#!/bin/bash\n%s\n' "$2" > "$STUBS/$1"
  chmod +x "$STUBS/$1"
}

setup() {
  WORK="$(mktemp -d)"
  export HOME="$WORK/home" STUBS="$WORK/stubs" CALLS="$WORK/calls" FAKE="$WORK/brew"
  mkdir -p "$HOME/Library/Fonts" "$STUBS" "$FAKE/opt/openjdk@21/libexec/openjdk.jdk/Contents/Home/bin" "$WORK/keystore"
  cp -r "$SRC" "$WORK/repo"
  touch "$CALLS" "$HOME/Library/Fonts/CaskaydiaCoveNerdFont-Regular.ttf"
  stub uname 'echo Darwin'
  stub brew 'echo "brew $*" >> "$CALLS"
case "$1" in
  --prefix) echo "$FAKE/opt/${2:-x}" ;;
  shellenv) echo "export PATH=$STUBS:\$PATH" ;;
esac'
  stub jenv 'echo "jenv $*" >> "$CALLS"
case "$1" in
  init) echo "true" ;;
  versions) [ -f "$HOME/.jenv21" ] && echo 21 ;;
  add) touch "$HOME/.jenv21" ;;
esac'
  stub ssh-keygen 'echo "ssh-keygen $*" >> "$CALLS"; touch "$HOME/.ssh/id_ed25519_work" "$HOME/.ssh/id_ed25519_work.pub"'
  stub ssh-add 'echo "ssh-add $*" >> "$CALLS"'
  stub mise 'echo "mise $*" >> "$CALLS"; [ "$1" = activate ] && echo true; exit 0'
  stub ghostty 'echo "ghostty $*" >> "$CALLS"'
  stub java 'echo "openjdk version \"21.0.1\""'
  for t in oh-my-posh fzf zoxide atuin delta eza bat mvn ng python3; do stub "$t" 'exit 0'; done
  command -v zsh >/dev/null || stub zsh 'java -version'
  cat > "$FAKE/opt/openjdk@21/libexec/openjdk.jdk/Contents/Home/bin/keytool" <<EOF
#!/bin/bash
action="\$1"
while [ "\$1" != -alias ]; do shift; done
echo "keytool \$action \$2" >> "$CALLS"
if [ "\$action" = -list ]; then [ -f "$WORK/keystore/\$2" ]; else touch "$WORK/keystore/\$2"; fi
EOF
  chmod +x "$FAKE/opt/openjdk@21/libexec/openjdk.jdk/Contents/Home/bin/keytool"
}

mk() { PATH="$STUBS:$PATH" make -C "$WORK/repo" "$@" 2>&1; }

setup
out="$(mk)"
check "should list the steps without installing anything when run without target" \
  "grep -q 'dotfiles' <<<\"\$out\" && ! grep -q bundle \"\$CALLS\""

setup
out="$(env -u GIT_NAME PATH="$STUBS:$PATH" make -C "$WORK/repo" all 2>&1)"; code=$?
check "should stop before any install when git identity is missing" \
  "[ $code -ne 0 ] && grep -q GIT_NAME <<<\"\$out\" && ! grep -q bundle \"\$CALLS\""

setup
rm "$STUBS/brew"
out="$(mk brew)"; code=$?
check "should point to Self Service when brew is missing" "[ $code -ne 0 ] && grep -q 'Self Service' <<<\"\$out\""

setup
stub uname 'echo Linux'
out="$(mk brew)"; code=$?
check "should refuse to run outside macOS" "[ $code -ne 0 ] && ! grep -q bundle \"\$CALLS\""

setup
printf 'export PATH="/selfservice/bin:$PATH"\neval "$(jenv init -)"\n' > "$HOME/.zshrc"
mk dotfiles >/dev/null
check "should link zshrc" "[ -L \"\$HOME/.zshrc\" ]"
check "should keep old zshrc lines in zshrc.local" "grep -q selfservice \"\$HOME/.zshrc.local\""
check "should not carry jenv init twice" "! grep -q 'jenv init' \"\$HOME/.zshrc.local\""
check "should load brew from where it really is" "grep -q \"\$STUBS/brew shellenv\" \"\$HOME/.zshrc.brew\""
check "should keep a backup of the old zshrc" "ls \"\$HOME\"/.zshrc.bak.* >/dev/null 2>&1"
before="$(wc -l < "$HOME/.zshrc.local")"
mk dotfiles >/dev/null
check "should not duplicate zshrc.local on rerun" "[ \"$before\" = \"\$(wc -l < \"\$HOME/.zshrc.local\")\" ]"

setup
mk prompt >/dev/null
check "should install the Windows prompt theme" "[ -L \"\$HOME/.config/oh-my-posh/tokyonight_stormx.omp.json\" ]"
check "should link ghostty config" "[ -L \"\$HOME/.config/ghostty/config\" ]"

setup
mkdir -p "$HOME/Workspace/certificates"
touch "$HOME/Workspace/certificates/root.pem" "$HOME/Workspace/certificates/apps.pem"
mk java >/dev/null
check "should import each certificate" "[ \"\$(grep -c 'keytool -importcert' \"\$CALLS\")\" = 2 ]"
check "should declare the JDK in jenv" "grep -q 'jenv add' \"\$CALLS\" && grep -q 'jenv global 21.0' \"\$CALLS\""
mk java >/dev/null
check "should not reimport nor readd on rerun" \
  "[ \"\$(grep -c 'keytool -importcert' \"\$CALLS\")\" = 2 ] && [ \"\$(grep -c 'jenv add' \"\$CALLS\")\" = 1 ]"

setup
out="$(mk java)"; code=$?
check "should warn and go on when no certificate is there yet" "[ $code -eq 0 ] && grep -q '.pem' <<<\"\$out\""

setup
stub brew 'echo "brew $*" >> "$CALLS"
case "$1" in
  --prefix) echo "$FAKE/opt/${2:-x}" ;;
  shellenv) echo "export PATH=$STUBS:\$PATH" ;;
  bundle) exit 1 ;;
esac'
out="$(mk all GIT_NAME=Q GIT_EMAIL=q@x.y)"; code=$?
check "should finish the other steps when one cask fails" \
  "[ -L \"\$HOME/.zshrc\" ] && grep -q 'ssh-keygen' \"\$CALLS\" && grep -q 'brew bundle' <<<\"\$out\""

setup
out="$(mk all GIT_NAME=Quentin GIT_EMAIL=q@x.y)"; code=$?
check "should run the whole install in one command" "[ $code -eq 0 ]"
check "should set the git identity" "[ \"\$(git config --global user.email)\" = q@x.y ]"
check "should sign commits with the work key" "[ \"\$(git config --global commit.gpgsign)\" = true ]"
check "should validate ghostty config" "grep -q 'ghostty +validate-config' \"\$CALLS\""

echo "---"; echo "$pass ok, $fail ko"
[ "$fail" -eq 0 ]
