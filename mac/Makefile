SHELL := /bin/bash
.DEFAULT_GOAL := help
.PHONY: help all check check-git brew dotfiles prompt java node ssh git doctor

GIT_NAME  ?= $(shell git config --global user.name 2>/dev/null)
GIT_EMAIL ?= $(shell git config --global user.email 2>/dev/null)
SSH_KEY   := $(HOME)/.ssh/id_ed25519_work
CERTS_DIR := $(HOME)/Workspace/certificates

BREW := $(shell command -v brew 2>/dev/null || ls /opt/homebrew/bin/brew /usr/local/bin/brew 2>/dev/null | head -1)
export PATH := $(dir $(BREW)):$(PATH)

define link
mkdir -p "$(dir $(2))"; \
if [ -e "$(2)" ] && [ ! -L "$(2)" ]; then mv "$(2)" "$(2).bak.$$(date +%Y%m%d%H%M%S)"; fi; \
ln -sfn "$(CURDIR)/$(1)" "$(2)"
endef

help: ## Liste les étapes
	@grep -E '^[a-z-]+:.*## ' $(MAKEFILE_LIST) | awk -F':.*## ' '{printf "  make %-10s %s\n", $$1, $$2}'

all: check check-git brew dotfiles prompt java node git doctor ## Tout installer
	@echo
	@echo "Fini. À faire à la main :"
	@echo "  1. pbcopy < $(SSH_KEY).pub, puis GitLab > Preferences > SSH Keys, usage 'Authentication & Signing'"
	@echo "  2. Remplace gitlab.TON-ENTREPRISE dans ~/.ssh/config, puis : ssh -T git@<hôte>"
	@echo "  3. Ouvre Docker Desktop une fois pour accepter la licence"
	@echo "  4. Ouvre Ghostty"

check: ## Vérifie macOS et Homebrew
	@[ "$$(uname -s)" = Darwin ] || { echo "Ce Makefile est fait pour macOS."; exit 1; }
	@[ -n "$(BREW)" ] || { echo "Homebrew introuvable : installe-le depuis l'app Self Service, puis relance."; exit 1; }

check-git:
	@[ -n "$(GIT_NAME)" ] && [ -n "$(GIT_EMAIL)" ] || { echo 'Manque ton identité : make all GIT_NAME="Prénom Nom" GIT_EMAIL="prenom.nom@entreprise.com"'; exit 1; }

brew: check ## Installe les paquets du Brewfile
	@echo "==> Paquets (long la première fois)"
	@"$(BREW)" bundle --file="$(CURDIR)/Brewfile" || echo "⚠️  brew bundle : au moins un paquet a échoué, la fin de make doctor dira lequel manque."

dotfiles: check ## Branche zshrc, mise et le gitignore global
	@echo "==> Dotfiles"
	@echo 'eval "$$($(BREW) shellenv)"' > "$(HOME)/.zshrc.brew"
	@if [ -f "$(HOME)/.zshrc" ] && [ ! -L "$(HOME)/.zshrc" ]; then \
		grep -v 'jenv init' "$(HOME)/.zshrc" >> "$(HOME)/.zshrc.local" || true; \
	fi
	@$(call link,dotfiles/zshrc,$(HOME)/.zshrc)
	@$(call link,dotfiles/mise-config.toml,$(HOME)/.config/mise/config.toml)
	@$(call link,dotfiles/gitignore_global,$(HOME)/.config/git/ignore)

prompt: check ## Pose le look Windows : thème Oh My Posh et config Ghostty
	@echo "==> Prompt et Ghostty"
	@$(call link,dotfiles/tokyonight_stormx.omp.json,$(HOME)/.config/oh-my-posh/tokyonight_stormx.omp.json)
	@$(call link,dotfiles/ghostty-config,$(HOME)/.config/ghostty/config)

java: check ## Java 21 : importe les certificats et déclare le JDK dans jenv
	@echo "==> Java 21"
	@set -e; shopt -s nullglob; \
	JVM="$$("$(BREW)" --prefix openjdk@21)/libexec/openjdk.jdk/Contents/Home"; \
	[ -x "$$JVM/bin/keytool" ] || { echo "openjdk@21 absent : lance make brew."; exit 1; }; \
	pems=("$(CERTS_DIR)"/*.pem); \
	[ $${#pems[@]} -gt 0 ] || echo "⚠️  Aucun .pem dans $(CERTS_DIR) : télécharge-les puis relance make java, sinon Maven ne joindra pas Artifactory."; \
	for f in "$${pems[@]}"; do \
		name="$$(basename "$$f" .pem)"; \
		"$$JVM/bin/keytool" -list -alias "$$name" -keystore "$$JVM/lib/security/cacerts" -storepass changeit >/dev/null 2>&1 || \
		"$$JVM/bin/keytool" -importcert -noprompt -alias "$$name" -file "$$f" -keystore "$$JVM/lib/security/cacerts" -storepass changeit; \
	done; \
	mkdir -p "$(HOME)/.jenv/versions"; \
	eval "$$(jenv init -)"; \
	jenv enable-plugin export >/dev/null || true; \
	jenv versions --bare | grep -q '^21$$' || jenv add "$$JVM"; \
	jenv global 21.0

node: check ## Node LTS via mise
	@echo "==> Node"
	@mise install || echo "⚠️  mise install a échoué (proxy ?) : le Node de Homebrew reste utilisable."

ssh: check-git ## Crée la clé SSH pro (avec passphrase)
	@echo "==> Clé SSH"
	@mkdir -p "$(HOME)/.ssh" && chmod 700 "$(HOME)/.ssh"
	@[ -f "$(SSH_KEY)" ] || ssh-keygen -t ed25519 -C "$(GIT_EMAIL)" -f "$(SSH_KEY)"
	@[ -f "$(HOME)/.ssh/config" ] || { cp dotfiles/ssh-config "$(HOME)/.ssh/config"; chmod 600 "$(HOME)/.ssh/config"; }
	@ssh-add --apple-use-keychain "$(SSH_KEY)"

git: check-git ssh ## Configure git et signe les commits avec la clé pro
	@echo "==> Git"
	@git config --global user.name "$(GIT_NAME)"
	@git config --global user.email "$(GIT_EMAIL)"
	@git config --global init.defaultBranch main
	@git config --global pull.rebase true
	@git config --global rebase.autoStash true
	@git config --global push.autoSetupRemote true
	@git config --global fetch.prune true
	@git config --global core.pager delta
	@git config --global interactive.diffFilter "delta --color-only"
	@git config --global delta.navigate true
	@git config --global merge.conflictStyle zdiff3
	@git config --global gpg.format ssh
	@git config --global user.signingkey "$(SSH_KEY).pub"
	@git config --global commit.gpgsign true
	@git config --global tag.gpgsign true

doctor: check ## Dit ce qui marche et ce qui manque
	@echo "==> Vérification"
	@missing=0; \
	for c in ghostty oh-my-posh jenv java mvn ng python3 delta eza bat fzf zoxide; do \
		command -v "$$c" >/dev/null || { echo "❌ $$c manquant"; missing=1; }; \
	done; \
	ls "$(HOME)"/Library/Fonts/*Caskaydia* >/dev/null 2>&1 || { echo "❌ police CaskaydiaCove manquante"; missing=1; }; \
	ghostty +validate-config || { echo "❌ config Ghostty refusée"; missing=1; }; \
	zsh -ic 'java -version' 2>&1 | grep -q '"21' || { echo "❌ java ne répond pas en 21 dans un nouveau shell"; missing=1; }; \
	[ $$missing -eq 0 ] && echo "✅ Tout est bon."; \
	exit $$missing
