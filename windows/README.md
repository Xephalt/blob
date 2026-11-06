# Windows : même terminal en une commande

Installe Oh My Posh et la police CaskaydiaCove (via `winget`, sans droits admin), pose le thème `tokyonight_stormx`, ajoute un bloc à tes profils PowerShell (couleurs de saisie, `ll`, `touch`, `mkcd`) et règle Windows Terminal (police + couleurs Campbell). Tes profils gardent leurs lignes, `settings.json` est sauvegardé en `.bak`. Relançable.

## Lancer

1. Récupère le repo (voir le [README principal](../README.md)) et **ouvre [`install.ps1`](install.ps1) pour le lire**.
2. Ouvre Windows Terminal une fois (sinon le réglage de police est sauté, le script le dit).
3. Dans PowerShell, depuis le dossier `windows` :
   ```powershell
   .\install.ps1
   ```
4. Ferme et rouvre Windows Terminal.

## Si PowerShell refuse de lancer le script

C'est la politique d'exécution de la machine. Sur un PC personnel, tu peux autoriser les scripts locaux pour ton compte :
```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
```
Si le fichier vient d'un ZIP téléchargé, débloque-le d'abord : `Unblock-File .\install.ps1`.

**Sur un poste d'entreprise, ne contourne pas la politique** (pas de `-ExecutionPolicy Bypass`) : demande à ton IT, ou installe Oh My Posh depuis le portail d'apps de l'entreprise.
