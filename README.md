
G1:
Q1 & Q2 : on recupere les fichiers chiffré et les zip chiffrée, effectivement on va tout séparer, dans le code, mais on doit s en occuper
Cependant on va prioriser le métier pour le moment

Q3 — Encodage des fichiers : La doc mentionne que les fichiers Filière sont en ISO-8859-1. Est-ce que tous les CSVs sont UTF-8, ou il y a d’autres exceptions à gérer ?

Je sais pas

Q4 — Structures CSV : Tu as les headers exacts des fichiers d’entrée quelque part ? Notamment Personne.csv (28 cols), UO.csv, JAD_JL.csv — ou on travaille uniquement par position de colonne comme dans le shell ?

On va tout fournir


Groupe 2 — Logique métier EdJad
Q5 — Personnes sans position JAD : Le shell faisait un inner join → perte de données. Ta règle : tout le monde sort dans le fichier final, avec les colonnes JAD vides si pas de match ? (LEFT JOIN semantics)
Q6 — Padding des codes JAD : Le shell ajoutait des zéros (000P, 00E, 0F, D). C’est une règle métier stable ou un artefact du shell à jeter ?
Q7 — Libellés FR/EN : Pour chaque niveau JAD (Domaine/Famille/Emploi/Position), on veut les 2 langues. Est-ce que parfois un libellé EN peut manquer ? Comportement attendu : vide ou fallback sur FR ?

---

Groupe 3 — Logique métier Refog
Q8 — Email groupe → direct : Le shell remplaçait l’email groupe par un email direct s’il existait. Cette logique vient d’où ? D’un autre fichier source, d’une colonne dans Personne.csv ?
Q9 — Hiérarchie UO : Le shell calculait 2 hiérarchies (opérationnelle = descendants, organisationnelle = ancêtres). Est-ce que le fichier UOHierarchique.csv donne déjà les relations parent-enfant, ou c’est calculé entièrement ?
Q10 — Filières vides : Le shell faisait un fallback UO_NOM quand la filière était vide. Cette règle est toujours valide ?

---

Groupe 4 — Delta désactivations
Q11 — Fichier J-1 : ImportRefogDesactVieille.csv sert de mémoire entre deux runs. On le stocke où ? Dans le même répertoire output ? Et il fait partie des 7 jours glissants ou c’est un fichier à part (1 seul, écrasé à chaque run) ?
Q12 — Premier run : Si le fichier J-1 n’existe pas (premier run), comportement attendu : on sort toutes les désactivées, ou on sort un fichier vide ?

---

Groupe 5 — Outputs & rétention
Q13 — 7 jours glissants : Convention de nommage ? Ex. ImportRefog_20260324.csv ? Et les 3 fichiers (FINAL, Desact, DesactVieille) ont tous les 7 jours, ou seulement le FINAL ?
Q14 — Headers : Le fichier FINAL doit avoir une ligne d’en-tête ? Le shell l’ajoutait via sed -i "1i $ENTETE". On garde ça ?
Q15 — CLI : Interface similaire à ReglissEnrichment — arguments pour les paths d’input/output, flag --dry-run ? Ou c’est un job batch avec config ?

---

Groupe 6 — Edge cases critiques
Q16 — Personne dans Refog mais absente d’EdJad : Ça peut arriver ? (Personne sans matricule EDGAHR ?) → sortie avec colonnes JAD vides ?
Q17 — Personne dans EdJad mais absente de Refog : Ça peut arriver ? → on la perd ou on la garde ?
Q18 — Doublons UIDs : Le shell ne gérait pas les doublons explicitement. Si un UID apparaît 2x dans Personne.csv, comportement attendu ?

