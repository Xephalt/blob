Allez, je te charge le sac. Je découpe par bloc d’argumentaire. Tout ce qui est mon jugement stratégique (pas un fait vérifiable) je le marque comme tel — le reste c’est du factuel technique standard.

1. Ton pitch d’ouverture (le “présente-toi”)

C’est LA question qui ouvre à 90%. Un junior qui répond bien ici prend 10 min d’avance. Structure en 3 temps, ~90 sec :

Présent → Preuve → Pourquoi eux.

“Je suis développeur Full Stack, principalement TypeScript/Node côté perso et freelance, mais avec une vraie exposition Java/Spring Boot. Ce qui me caractérise, c’est une discipline d’architecture et de tests : j’écris du code testable, je le fais évoluer par petits incréments, et je me méfie de la sur-ingénierie. Concrètement, en ce moment je forme même une cohorte sur les pratiques DevOps — Docker, CI/CD, Kubernetes. Et ce qui m’attire ici, c’est le domaine : une IHM qui expose de la donnée compta/risque, c’est un contexte où la fiabilité et la rigueur comptent vraiment, et c’est exactement là que je veux progresser.”

Mon take : ta carte maîtresse cachée, c’est que tu formes des gens (dont une cohorte BNP). Ça envoie un signal énorme : tu maîtrises assez pour enseigner, et tu es coachable/pédagogue. Place-le tôt, discrètement, sans en faire des tonnes.

2. Le narratif TS→Java (tu VAS te le prendre)

“Ton stack c’est TypeScript, pourquoi Java ?” — prépare-le, c’est quasi garanti.

Ne te défends pas, retourne-le en force :

“Justement, TypeScript et Java partagent beaucoup : typage fort, POO, écosystème mature. Passer de l’un à l’autre, pour moi, c’est surtout de la syntaxe et des idiomes, pas un changement de paradigme. Spring et NestJS reposent d’ailleurs sur les mêmes principes — injection de dépendances, décorateurs/annotations, architecture en couches. J’ai déjà les fondamentaux ; ce que je veux, c’est les ancrer sur du Java de production dans un vrai contexte métier.”

Mon take : ce parallèle NestJS↔Spring est ta meilleure arme. Tu connais NestJS à fond (c’est dans ton cœur de stack). Dis-le : “NestJS est littéralement inspiré de Spring — DI, modules, décorateurs. Le mental model, je l’ai déjà.” C’est vrai et ça désamorce complètement le doute sur ton “manque” de Java.

3. Les questions archi — où placer ta vision SANS être dogmatique

Comme promis. Le but : montrer que tu sais quand ne PAS sur-architecturer.

“Comment tu structures une appli ?”

“Je pars simple : les couches classiques controller/service/repository suffisent pour l’immense majorité des cas. J’isole la logique métier surtout pour pouvoir la tester vite et sans base de données. Je n’ajoute un pattern ou une abstraction que le jour où un test ou une contrainte métier réelle me le demande — jamais par principe esthétique.”

“Tu connais la Clean Architecture / l’hexagonal ?”

“Oui, et je m’en sers avec parcimonie. L’idée que je retiens surtout, c’est la testabilité : pouvoir tester ma logique métier en isolation, avec des implémentations in-memory à la place de la vraie infra. Mais sur un CRUD ou une IHM, je ne vais pas sortir l’artillerie hexagonale — ce serait du coût sans bénéfice.”

Mon take : cette réponse est un piège inversé. La plupart des juniors qui connaissent la Clean Archi la sur-vendent. Toi tu montres du discernement, ce qui est exactement ce qu’une équipe banque veut entendre. Le mot magique à placer : “coût sans bénéfice” — ça prouve que tu penses en ingénieur, pas en fan.

4. Les questions à LEUR poser (énorme différenciateur)

Un junior qui pose de bonnes questions se démarque plus que par n’importe quelle réponse technique. Prépare-en 4-5, sors-en 2-3.

	•	“Concrètement, l’IHM c’est quel front ? Angular, React, ou du plus historique ?” → montre que tu penses stack réelle (et tu as Angular dans les mains).
	•	“Comment vous testez ? Il y a une culture de tests automatisés dans l’équipe, du CI/CD ?” → tu ramènes le terrain sur ta force DevOps.
	•	“Le datawarehouse compta/risque, c’est de la donnée réglementaire ? Il y a des enjeux de conformité qui cadrent ce que l’IHM peut afficher ?” → montre une curiosité métier rare chez un junior.
	•	“Sur SRS Vision, vous êtes plutôt en création d’un nouveau module ou en reprise d’existant ?” → tu montres que tu anticipes la réalité du legacy.
	•	“Comment vous accompagnez un junior sur les premiers mois ?” → signal de coachabilité.

Mon take : la question conformité/réglementaire est ton money move. Personne ne l’attend d’un junior dev. En banque, montrer que tu comprends que le code sert un enjeu de contrôle et de risque, ça te fait passer de “codeur” à “futur ingénieur qui comprend le métier”.

5. Le domaine métier — parle leur langue

Tu n’as pas besoin d’être expert, juste de montrer que tu as compris le sens du projet. Le contexte du message : IHM = interface entre le datawarehouse Compta/Risque et les utilisateurs.

Traduction que tu peux formuler :

“Si je comprends bien, il y a une grosse base de données analytique côté compta/risque, et l’enjeu c’est de rendre cette donnée exploitable et lisible pour des utilisateurs métier via une interface. Donc les vrais défis, c’est probablement la performance des requêtes sur de gros volumes, la justesse des chiffres affichés, et l’ergonomie pour des utilisateurs non-techniques.”

Vocabulaire banque utile à connaître : SRS = probablement lié au reporting réglementaire/risque (je ne peux pas confirmer le sens exact de “SRS Vision”, c’est un projet interne BNP — ne bluffe pas dessus, pose plutôt la question). Compta/Risque = les fonctions qui mesurent l’exposition de la banque et produisent les états financiers/réglementaires.

6. Ton atout massif sous-exploité : le DevOps

Tu formes des gens sur Docker/CI/CD/K8s. Sur un poste dev junior, c’est une compétence de senior. La plupart des juniors ne savent pas déployer.

À placer si on parle de workflow/livraison :

“J’ai une vraie culture CI/CD — je forme même dessus. Donc au-delà d’écrire la feature, je sais penser build, tests automatisés en pipeline, conteneurisation. Pour une équipe, ça veut dire un junior qui n’a pas besoin qu’on lui tienne la main sur la partie livraison.”

Mon take : c’est peut-être ton argument le plus sous-coté. Garde-le en réserve et sors-le au bon moment — ça repositionne toute la perception de ton niveau.

7. Behavioral — les classiques banque

	•	“Un désaccord technique avec un collègue ?” → montre que tu argumentes puis que tu disagree & commit. En banque, l’esprit d’équipe > avoir raison.
	•	“Ta plus grande faiblesse ?” → l’honnête qui marche pour toi : “Mon Java de production est moins rodé que mon TypeScript — mais les fondamentaux sont là et je monte vite, la preuve j’enseigne des stacks que je dois maîtriser à fond.” Faiblesse réelle + preuve que tu la compenses.
	•	“Pourquoi la banque ?” → rigueur, exigence de fiabilité, données à fort enjeu, envie de bosser sur des systèmes qui comptent.

Deux options pour la suite, dis-moi :

	1.	Je te fais un mini-kata Java/Spring à coder ce soir (30 min, un @RestController + @Service + JpaRepository from scratch) — le meilleur moyen de dérouiller le Java réel avant demain.
	2.	Je te prépare une fiche SQL (jointures, agrégations, GROUP BY, fenêtrage) vu que le datawarehouse va probablement peser dans les questions techniques.

Bonne demande — les patterns et les annotations, c’est le genre de questions “checklist” qu’un intervieweur balance pour jauger ta culture générale. Voici de quoi couvrir large.

Design patterns — ceux qu’on peut te demander de citer

Je te mets : à quoi ça sert (1 ligne) + quand tu l’utilises + un mot où Spring l’utilise (ça fait mouche de le relier au framework).

Singleton — une seule instance partagée dans toute l’appli. → Les beans Spring sont des singletons par défaut. C’est le pattern que tu utilises sans le savoir à chaque @Service.

Factory / Factory Method — déléguer la création d’objets à une méthode/classe dédiée plutôt que d’appeler new partout. → Utile quand la logique de création est complexe ou dépend d’une condition. Spring : le conteneur IoC est essentiellement une immense factory de beans.

Strategy — encapsuler plusieurs algorithmes interchangeables derrière une même interface, choisis à l’exécution. → L’exemple canonique : plusieurs façons de calculer un truc (ex. plusieurs modes de calcul de risque/frais), tu injectes la bonne implémentation.

interface CalculStrategy { BigDecimal calculer(Montant m); }
// StandardCalcul, PremiumCalcul... injectées selon le contexte


Builder — construire un objet complexe étape par étape, surtout quand il a beaucoup de champs optionnels. → Évite les constructeurs à 12 paramètres. Tu le connais déjà via Lombok @Builder.

Adapter — faire dialoguer deux interfaces incompatibles en les “traduisant”. → C’est exactement l’idée derrière tes ports/adapters : brancher une techno externe sur ton interface à toi.

Observer — un objet notifie automatiquement ses abonnés d’un changement. → Spring l’utilise dans son système d’ApplicationEvent / @EventListener.

Dependency Injection (souvent cité comme pattern) — fournir les dépendances de l’extérieur au lieu de les créer soi-même. → Le cœur de Spring. Si tu ne devais en maîtriser qu’un, c’est celui-là.

Proxy — un objet intermédiaire qui contrôle l’accès à un autre. → Spring l’utilise en douce : @Transactional et l’AOP fonctionnent via des proxies générés autour de tes beans. Bon point bonus si tu le sais.

Template Method — définir le squelette d’un algo dans une méthode, en laissant les sous-classes remplir certaines étapes. → Spring : les classes ...Template (JdbcTemplate, RestTemplate).

Repository — abstraire l’accès aux données derrière une interface orientée “collection d’objets”. → Littéralement JpaRepository.

Mon take (jugement, pas un fait) : ne récite pas le catalogue GoF comme un perroquet. Si on te demande “tu connais des design patterns ?”, la meilleure réponse n’est pas d’en lister 15, c’est :

“Oui, mais je m’en méfie autant que je les utilise. Je n’introduis un pattern que quand un besoin réel le justifie — sinon c’est de la complexité gratuite. Ceux que j’utilise le plus naturellement : Strategy quand j’ai de la vraie variabilité d’algorithme, Builder pour les objets complexes, et l’injection de dépendances au quotidien via Spring.”

Ça montre que tu connais et que tu as du discernement. Un junior qui sait quand ne pas sortir un pattern impressionne plus que celui qui les connaît tous.

Annotations Spring / Java à connaître

Je te classe par famille. Celles en gras = celles où on peut te demander d’expliquer, pas juste de citer.

Stéréotypes / composants (le conteneur)

	•	@Component — bean générique géré par Spring
	•	@Service — bean de couche métier (sémantique)
	•	@Repository — bean d’accès données (+ traduction des exceptions de persistance)
	•	@Controller / @RestController — bean web (REST = + @ResponseBody)
	•	@Configuration + @Bean — classe de config qui déclare des beans manuellement
	•	@Autowired — injection (mais rappelle : tu préfères l’injection par constructeur, souvent sans annotation)

Web / REST (Spring MVC)

	•	@RequestMapping — mapping URL général (à la classe)
	•	@GetMapping / @PostMapping / @PutMapping / @DeleteMapping — raccourcis par verbe HTTP
	•	@PathVariable — extraire une variable de l’URL (/users/{id})
	•	@RequestParam — extraire un paramètre de query (?statut=actif)
	•	@RequestBody — désérialiser le corps JSON en objet Java
	•	@ResponseStatus — forcer un code HTTP de retour

JPA / Persistence

	•	@Entity — classe mappée sur une table
	•	@Id + @GeneratedValue — clé primaire (+ stratégie de génération)
	•	@Column / @Table — mapping colonne/table
	•	@OneToMany / @ManyToOne / @ManyToOne / @OneToOne — relations (sache expliquer le sens + lazy/eager)
	•	@JoinColumn — la colonne de clé étrangère
	•	@Query — écrire ta requête JPQL ou SQL natif toi-même

Spring Boot / config

	•	@SpringBootApplication — l’annotation racine (= @Configuration + @EnableAutoConfiguration + @ComponentScan). Sache la décomposer, c’est une question piège fréquente.
	•	@Value — injecter une valeur de config (application.properties)
	•	@Profile — activer un bean selon l’environnement (dev/prod)

Transactions

	•	@Transactional — rend une méthode transactionnelle (commit/rollback auto). Sache dire que ça marche via un proxy (lien avec le pattern Proxy plus haut !), et que par défaut ça rollback sur les exceptions unchecked seulement.

Tests

	•	@SpringBootTest — charge le contexte Spring complet (test d’intégration)
	•	@WebMvcTest / @DataJpaTest — charge seulement une tranche (web / JPA), plus rapide
	•	@Test — (JUnit) marque une méthode de test
	•	@Mock / @InjectMocks — (Mockito) créer et injecter des mocks
	•	@BeforeEach / @AfterEach — setup/teardown avant chaque test

Lombok (si l’équipe l’utilise — très courant)

	•	@Getter / @Setter, @Data, @Builder, @AllArgsConstructor / @NoArgsConstructor, @RequiredArgsConstructor (ce dernier est top pour l’injection par constructeur : il génère le constructeur avec tes champs final)

Les 3 questions pièges classiques sur les annotations :

	1.	“Décompose @SpringBootApplication” → @Configuration + @EnableAutoConfiguration + @ComponentScan.
	2.	“Différence @Component / @Service / @Repository ?” → techniquement tous des beans, la différence est sémantique (+ @Repository traduit les exceptions). Tu l’as déjà vue.
	3.	“Comment marche @Transactional ?” → proxy autour du bean, ouvre une transaction avant la méthode, commit après, rollback si exception unchecked.

Deux trucs pour finir :

	•	Le combo qui claque en entretien : relie un pattern à une annotation. Genre ”@Transactional c’est un Proxy”, ou “le conteneur Spring c’est une grosse Factory de Singletons”. Ça montre que tu ne récites pas des listes, tu comprends la mécanique dessous.
	•	Je te repose la question d’avant parce qu’elle vaut vraiment le coup ce soir : tu veux que je te génère un petit projet Spring Boot complet et minimal (une entité, un repository, un service, un controller REST) que tu tapes à la main en 30 min ? Rien ne dérouille le Java aussi vite que d’écrire du vrai code juste avant. Je te le fais en TDD si tu veux, dans ton format habituel (test rouge → tu implémentes → tu me montres le diff).