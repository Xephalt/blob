Parfait. On va faire exactement ce que tu demandes, dans l’ordre, sans code, sans détour, sans “peut-être”.
Juste l’intention métier, les opérations attendues, comment les tables sont utilisées, puis quoi construire / quoi réutiliser, et enfin l’URL + le DTO final.

⸻

1️⃣ Intention métier complète (ce que fait réellement la carte)

Mesurer et comparer l’impact énergétique et carbone de l’usage d’un modèle LLM sur une période donnée, avec des filtres précis, et présenter une évolution compréhensible pour un humain.

Plus concrètement, la carte doit répondre à ces questions :
	1.	Quel est l’impact carbone total des messages générés sur la période sélectionnée ?
	2.	Quel est l impact moyen par conversation (pour normaliser l’usage) ?
	3.	Quelle est l’énergie consommée par token (métrique technique explicative) ?
	4.	Comment ces valeurs évoluent par rapport à la période précédente équivalente ?
	5.	Avec une unité lisible et cohérente, décidée par la logique métier.

La carte ne cherche pas :
	•	à expliquer le détail par jour
	•	à afficher les tokens bruts
	•	à faire un breakdown par modèle ou par user

Elle raconte une synthèse comparative.

⸻

2️⃣ Opérations métier attendues (pas techniques)

Pour chaque métrique affichée, la carte applique le même pipeline métier :

Étape A — Définir les périodes
	•	Une période courante (date_from → date_to)
	•	Une période de comparaison automatiquement déduite (même durée, juste avant)

👉 Cette logique existe déjà chez toi via
Period → PeriodResolver → ResolvedPeriod

⸻

Étape B — Filtrer le périmètre des données

Les données prises en compte doivent respecter tous ces critères :
	•	Message créé dans la période
	•	Message créé un jour de semaine autorisé (weekdays)
	•	Message généré par le modèle demandé (model)

⸻

Étape C — Agréger des valeurs brutes

Pour chaque période (courante + précédente), on calcule :
	•	Carbone total (en kgCO2eq)
	•	Carbone moyen par conversation (en kgCO2eq)
	•	Énergie totale par token (en kWh/token)

⚠️ À ce stade :
	•	aucune unité “humaine”
	•	aucun pourcentage
	•	aucun trend
	•	uniquement des nombres canoniques

⸻

Étape D — Comparer

Pour chaque métrique :
	•	delta = current - previous
	•	evolution = delta / previous * 100
	•	trend = UP | DOWN | STABLE

👉 Cette logique utilise ton MetricDto et ton Trend existants.

⸻

Étape E — Choisir l’unité (métier)

À partir de la valeur current uniquement :
	•	choisir l’unité la plus lisible (g, kg, t)
	•	convertir previous et delta dans la même unité
	•	arrondir de manière raisonnable

👉 Cette logique vit dans le Use Case, pas ailleurs.

⸻

3️⃣ Comment utiliser les tables (lecture métier)

message

C’est la source principale.

On l’utilise pour :
	•	created_at → période + weekday
	•	model_llm → filtre modèle
	•	token_input + token_output → volume de tokens
	•	conversation_id → rattachement métier

⸻

conversation

C’est l’unité métier de normalisation.

On l’utilise pour :
	•	compter le nombre de conversations distinctes
	•	calculer une moyenne “par conversation”

⸻

energy_usage

C’est la table de conversion énergétique.

On l’utilise pour :
	•	carbon_kg → impact carbone
	•	energy_kwh → énergie consommée

👉 Elle est jointe à message, jamais interrogée seule.

⸻

Résumé table → rôle

Table	Rôle métier
message	événement technique
conversation	unité métier
energy_usage	conversion environnementale


⸻

4️⃣ Fichiers à construire / modifier

À UTILISER (déjà existants)
	•	Application/Common/Period/Period
	•	Application/Common/Period/PeriodResolver
	•	Application/Common/Period/ResolvedPeriod
	•	Application/Common/Metrics/Trend
	•	Application/Admin/Dto/MetricDto

👉 Aucun changement ici.

⸻

À CONSTRUIRE

Application
	•	Application/Admin/Query/EnergyUsageMetrics
	•	porte ResolvedPeriod, weekdays, model
	•	Application/Admin/UseCase/GetEnergyUsageMetrics
	•	orchestre
	•	appelle le repository
	•	calcule delta / evolution / trend
	•	choisit l’unité
	•	construit le DTO final
	•	Application/Admin/Dto/EnergyUsageKpiDto
	•	DTO de la carte (agrégat final)

⸻

Infrastructure
	•	Repository/EnergyUsageMetricsRepository
	•	fait uniquement les agrégations SQL
	•	retourne des valeurs brutes (kg, kWh)

⸻

Delivery
	•	Controller/Admin/KpiController
	•	expose l’endpoint
	•	résout la période
	•	appelle le Use Case
	•	retourne le DTO

⸻

5️⃣ URL à appeler

GET /admin/kpi/energy-usage
  ?date_from=2025-11-09
  &date_to=2025-12-09
  &weekdays=1,2
  &model=gpt-4o


⸻

6️⃣ DTO final attendu (exact)

{
  "carbonImpact": {
    "current": 243,
    "previous": 202,
    "delta": 41,
    "evolution": 20.1,
    "trend": "up",
    "unit": "gCO2eq"
  },
  "averagePerConversation": {
    "current": 2.43,
    "previous": 2.57,
    "delta": -0.14,
    "evolution": -5.4,
    "trend": "down",
    "unit": "gCO2eq"
  },
  "energyPerToken": {
    "current": 0.0052,
    "previous": 0.0043,
    "delta": 0.0009,
    "evolution": 20.1,
    "trend": "up",
    "unit": "kWh"
  },
  "currentPeriod": {
    "from": "2025-11-09",
    "to": "2025-12-09"
  },
  "comparisonPeriod": {
    "from": "2025-10-09",
    "to": "2025-11-08"
  }
}


⸻

Phrase de clôture (à garder en tête)

Cette carte n’est pas un reporting SQL.
C’est un use case métier de lecture, qui transforme des événements techniques en une histoire compréhensible et comparable.

Si tu veux, au prochain message on peut faire une revue ultra-critique de ton GetUserMetrics actuel pour vérifier qu’il respecte exactement ces règles.