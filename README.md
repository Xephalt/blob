Très bien. Là on est clairement dans un artefact UI à produire, donc je mets mon sarcasme au vestiaire et je te livre un template Twig V1 propre, lisible, fidèle à la carte, avec valeurs en dur pour poser le cadre.

Objectif :
	•	lisible
	•	copiable
	•	facilement branchable plus tard sur ton DTO
	•	aucune logique métier dans le Twig

⸻

Intention UI (avant le code, 30 secondes)

La carte montre 3 niveaux d’information :
	1.	Impact total sur la période
→ valeur principale + évolution
	2.	Normalisation métier
→ moyenne par conversation + évolution
	3.	Métrique explicative
→ énergie par token + évolution + source

Visuellement :
	•	hiérarchie verticale claire
	•	flèches + couleurs = simple signal, pas du graphisme lourd
	•	aucune interaction JS en V1

⸻

Template Twig – V1 (_kpi_energy_usage_card.html.twig)

Valeurs en dur, comme demandé.
Tu brancheras les variables ensuite sans toucher à la structure.

<div class="kpi-card kpi-energy">
    <div class="kpi-card__header">
        <span class="kpi-card__title">Impact sur la période</span>
    </div>

    <div class="kpi-card__body">
        {# --- Impact carbone total --- #}
        <div class="kpi-metric kpi-metric--primary">
            <div class="kpi-metric__label">
                Équivalent carbone émis total
                <span class="kpi-metric__unit">(en kgCO2eq)</span>
            </div>

            <div class="kpi-metric__value">
                0,243 <span class="kpi-metric__value-unit">kg</span>
            </div>

            <div class="kpi-metric__evolution kpi-metric__evolution--up">
                ▲ +20,1&nbsp;%
            </div>
        </div>

        <div class="kpi-divider"></div>

        {# --- Moyenne par conversation --- #}
        <div class="kpi-metric kpi-metric--secondary">
            <div class="kpi-metric__label">
                Soit en moyenne / conversation
            </div>

            <div class="kpi-metric__value">
                2,43 <span class="kpi-metric__value-unit">g</span>
            </div>

            <div class="kpi-metric__evolution kpi-metric__evolution--down">
                ▼ -5,4&nbsp;%
            </div>
        </div>

        <div class="kpi-divider"></div>

        {# --- Énergie par token --- #}
        <div class="kpi-metric kpi-metric--tertiary">
            <div class="kpi-metric__label">
                Électricité nécessaire / token
            </div>

            <div class="kpi-metric__inline">
                <span class="kpi-metric__inline-value">
                    0,0052&nbsp;Wh
                </span>
                <span class="kpi-metric__inline-evolution kpi-metric__evolution--up">
                    (+20,1&nbsp;%)
                </span>
            </div>

            <div class="kpi-metric__source">
                Source : <strong>ecobalyse</strong> – Mix énergétique : France 🇫🇷
            </div>
        </div>
    </div>
</div>


⸻

Pourquoi cette structure est saine

1️⃣ Aucun calcul dans le Twig

Tout ce qui est :
	•	pourcentage
	•	flèche
	•	unité

👉 vient du DTO, pas du template.

⸻

2️⃣ Les blocs correspondent exactement au métier
	•	kpi-metric--primary → impact global
	•	kpi-metric--secondary → normalisation
	•	kpi-metric--tertiary → explication technique

Quand tu branches les données, tu sais où injecter quoi sans réfléchir.

⸻

3️⃣ Les classes CSS sont intentionnelles

Tu peux faire :
	•	une V1 full CSS statique
	•	une V2 avec thème sombre
	•	une V3 responsive

sans toucher au Twig.

⸻

Mapping futur avec ton DTO (pour quand tu brancheras)

Juste pour clarté mentale, sans coder maintenant :

carbonImpact.current
carbonImpact.unit
carbonImpact.evolution
carbonImpact.trend

averagePerConversation.current
averagePerConversation.unit
averagePerConversation.evolution
averagePerConversation.trend

energyPerToken.current
energyPerToken.unit
energyPerToken.evolution


⸻

Ce que je n’ai volontairement PAS fait
	•	❌ pas de JS
	•	❌ pas de logique if trend == up
	•	❌ pas de formatage dynamique
	•	❌ pas de composant Twig abstrait

C’est une V1 lisible et fidèle, pas un framework UI.

⸻

Si tu veux, prochaine étape possible :
	•	te proposer le CSS minimal qui rend exactement comme la carte
	•	ou te montrer la version Twig branchée sur le DTO
	•	ou faire une V2 responsive / dark

Mais là, tu as un socle propre.