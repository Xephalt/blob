<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prep entretien BNP — Java Full Stack</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;450;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --paper:#F6F5F1;
    --card:#FFFFFF;
    --ink:#1B1E27;
    --ink-soft:#4A4F5C;
    --line:#E4E1D8;
    --say:#B26A16;        /* à dire */
    --say-bg:#FBF2E2;
    --take:#5A4B8C;       /* stratégie */
    --take-bg:#EFECF7;
    --trap:#B03A25;       /* piège */
    --trap-bg:#F8E7E2;
    --code-bg:#1E2230;
    --code-ink:#E7E4DA;
    --accent:#1F3A5F;     /* structure */
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html{scroll-behavior:smooth;scroll-padding-top:74px}
  body{
    background:var(--paper);
    color:var(--ink);
    font-family:'Inter',system-ui,sans-serif;
    font-size:16px;
    line-height:1.6;
    -webkit-font-smoothing:antialiased;
  }
  .wrap{max-width:820px;margin:0 auto;padding:0 20px}

  /* ---------- HEADER ---------- */
  header{
    background:var(--accent);
    color:#F6F5F1;
    padding:34px 0 30px;
  }
  header .eyebrow{
    font-family:'JetBrains Mono',monospace;
    font-size:12px;letter-spacing:.14em;text-transform:uppercase;
    color:#9DB4CE;margin-bottom:10px;
  }
  header h1{
    font-family:'Space Grotesk',sans-serif;
    font-weight:700;font-size:32px;line-height:1.1;letter-spacing:-.01em;
  }
  header .meta{
    margin-top:14px;display:flex;flex-wrap:wrap;gap:8px;
  }
  header .tag{
    font-family:'JetBrains Mono',monospace;font-size:12px;
    background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);
    padding:4px 10px;border-radius:4px;color:#DCE6F2;
  }

  /* ---------- NAV ---------- */
  nav{
    position:sticky;top:0;z-index:50;
    background:rgba(246,245,241,.92);
    backdrop-filter:blur(8px);
    border-bottom:1px solid var(--line);
  }
  nav .scroller{
    display:flex;gap:6px;overflow-x:auto;padding:10px 20px;
    max-width:820px;margin:0 auto;
    -ms-overflow-style:none;scrollbar-width:none;
  }
  nav .scroller::-webkit-scrollbar{display:none}
  nav a{
    flex:0 0 auto;text-decoration:none;
    font-family:'JetBrains Mono',monospace;font-size:12.5px;
    color:var(--ink-soft);background:var(--card);
    border:1px solid var(--line);border-radius:20px;
    padding:6px 13px;white-space:nowrap;transition:.15s;
  }
  nav a:hover{border-color:var(--accent);color:var(--accent)}

  /* ---------- SECTIONS ---------- */
  section{padding:30px 0 6px}
  .sec-head{display:flex;align-items:baseline;gap:12px;margin-bottom:6px}
  .sec-num{
    font-family:'JetBrains Mono',monospace;font-size:13px;
    color:var(--say);font-weight:500;
  }
  h2{
    font-family:'Space Grotesk',sans-serif;
    font-weight:600;font-size:23px;letter-spacing:-.01em;
  }
  .sub{color:var(--ink-soft);font-size:14.5px;margin:2px 0 16px}
  p{margin:0 0 12px}
  p.body{color:var(--ink);font-size:15.5px}
  .divider{height:1px;background:var(--line);margin:26px 0 0}

  /* ---------- BLOCKS ---------- */
  .block{
    border-radius:9px;padding:14px 16px 14px 18px;margin:12px 0;
    border-left:4px solid;position:relative;
  }
  .block .label{
    font-family:'JetBrains Mono',monospace;font-size:11px;
    letter-spacing:.1em;text-transform:uppercase;font-weight:500;
    display:inline-block;margin-bottom:6px;
  }
  .say{background:var(--say-bg);border-color:var(--say)}
  .say .label{color:var(--say)}
  .say p{font-size:15.5px;line-height:1.55}
  .say em{font-style:normal;font-weight:600;color:var(--say)}

  .take{background:var(--take-bg);border-color:var(--take)}
  .take .label{color:var(--take)}
  .take p:last-child{margin-bottom:0}

  .trap{background:var(--trap-bg);border-color:var(--trap)}
  .trap .label{color:var(--trap)}
  .trap p:last-child{margin-bottom:0}

  /* code */
  pre{
    background:var(--code-bg);color:var(--code-ink);
    border-radius:9px;padding:14px 16px;margin:12px 0;
    overflow-x:auto;font-family:'JetBrains Mono',monospace;
    font-size:13.5px;line-height:1.55;
  }
  code{font-family:'JetBrains Mono',monospace;font-size:.88em;
    background:#EDEBE3;padding:1px 5px;border-radius:4px;color:#3A3F4C}

  /* listes questions */
  ul.ask{list-style:none;margin:10px 0}
  ul.ask li{
    background:var(--card);border:1px solid var(--line);
    border-radius:8px;padding:12px 14px;margin-bottom:9px;
  }
  ul.ask li .q{font-weight:500}
  ul.ask li .why{
    display:block;margin-top:5px;font-size:13.5px;color:var(--ink-soft);
    font-style:italic;
  }

  /* reference cards (patterns / annotations) */
  details{
    background:var(--card);border:1px solid var(--line);
    border-radius:10px;margin:12px 0;overflow:hidden;
  }
  details summary{
    cursor:pointer;padding:14px 16px;font-family:'Space Grotesk',sans-serif;
    font-weight:600;font-size:16px;list-style:none;
    display:flex;align-items:center;justify-content:space-between;
  }
  details summary::-webkit-details-marker{display:none}
  details summary::after{content:'+';font-family:'JetBrains Mono',monospace;
    color:var(--say);font-size:18px}
  details[open] summary::after{content:'–'}
  details .content{padding:0 16px 8px}

  .item{padding:11px 0;border-top:1px solid var(--line)}
  .item .name{font-family:'JetBrains Mono',monospace;font-weight:500;
    color:var(--accent);font-size:14px}
  .item .desc{font-size:14.5px;color:var(--ink);margin-top:2px}
  .item .spring{font-size:13px;color:var(--say);margin-top:3px;font-style:italic}

  .pill{display:inline-block;font-family:'JetBrains Mono',monospace;
    font-size:12.5px;background:#EDEBE3;color:#3A3F4C;
    padding:2px 7px;border-radius:5px;margin:2px 3px 2px 0}
  .fam{font-family:'Space Grotesk',sans-serif;font-weight:600;
    font-size:14px;color:var(--accent);margin:14px 0 4px}
  .fam:first-child{margin-top:4px}
  .note{font-size:13.5px;color:var(--ink-soft);margin-top:4px}

  footer{padding:30px 0 50px;color:var(--ink-soft);font-size:13.5px;text-align:center}

  @media (max-width:600px){
    header h1{font-size:26px}
    h2{font-size:20px}
    body{font-size:15px}
  }
  @media print{
    nav{display:none}
    header{background:#fff;color:#000;border-bottom:2px solid #000}
    header .eyebrow,header .tag{color:#333}
    details{border:1px solid #ccc}
    details:not([open]) summary::after{content:'(déplié à l’écran)';font-size:11px}
    pre{background:#f0f0f0;color:#000;border:1px solid #ccc}
  }
</style>
</head>
<body>

<header>
  <div class="wrap">
    <div class="eyebrow">Entretien préalable · SDE08 · SRS Vision</div>
    <h1>Prep entretien BNP Paribas</h1>
    <div class="meta">
      <span class="tag">Java Full Stack — junior</span>
      <span class="tag">45 min</span>
      <span class="tag">recommandé en interne</span>
    </div>
  </div>
</header>

<nav>
  <div class="scroller">
    <a href="#pitch">01 Pitch</a>
    <a href="#tsjava">02 TS→Java</a>
    <a href="#archi">03 Archi</a>
    <a href="#questions">04 À demander</a>
    <a href="#metier">05 Métier</a>
    <a href="#devops">06 DevOps</a>
    <a href="#behavioral">07 Behavioral</a>
    <a href="#patterns">08 Patterns</a>
    <a href="#annotations">09 Annotations</a>
    <a href="#pieges">10 Pièges</a>
  </div>
</nav>

<div class="wrap">

  <!-- 01 -->
  <section id="pitch">
    <div class="sec-head"><span class="sec-num">01</span><h2>Pitch d'ouverture</h2></div>
    <p class="sub">Le « présente-toi » ouvre à 90%. Structure ~90 sec : <strong>Présent → Preuve → Pourquoi eux.</strong></p>
    <div class="block say">
      <span class="label">À dire</span>
      <p>« Je suis développeur Full Stack, principalement TypeScript/Node côté perso et freelance, mais avec une vraie exposition Java/Spring Boot. Ce qui me caractérise, c'est une discipline d'architecture et de tests : j'écris du code testable, je le fais évoluer par petits incréments, et je me méfie de la sur-ingénierie. Concrètement, en ce moment je <em>forme même une cohorte sur les pratiques DevOps</em> — Docker, CI/CD, Kubernetes. Et ce qui m'attire ici, c'est le domaine : une IHM qui expose de la donnée compta/risque, c'est un contexte où la fiabilité et la rigueur comptent vraiment, et c'est exactement là que je veux progresser. »</p>
    </div>
    <div class="block take">
      <span class="label">Mon take</span>
      <p>Ta carte maîtresse cachée : <strong>tu formes des gens</strong> (dont une cohorte BNP). Signal énorme — tu maîtrises assez pour enseigner, et tu es coachable/pédagogue. Place-le tôt, discrètement, sans en faire des tonnes.</p>
    </div>
    <div class="divider"></div>
  </section>

  <!-- 02 -->
  <section id="tsjava">
    <div class="sec-head"><span class="sec-num">02</span><h2>Le narratif TS → Java</h2></div>
    <p class="sub">« Ton stack c'est TypeScript, pourquoi Java ? » — quasi garanti. Ne te défends pas, retourne-le.</p>
    <div class="block say">
      <span class="label">À dire</span>
      <p>« Justement, TypeScript et Java partagent beaucoup : typage fort, POO, écosystème mature. Passer de l'un à l'autre, pour moi, c'est surtout de la syntaxe et des idiomes, pas un changement de paradigme. Spring et NestJS reposent d'ailleurs sur les mêmes principes — injection de dépendances, décorateurs/annotations, architecture en couches. J'ai déjà les fondamentaux ; ce que je veux, c'est les ancrer sur du Java de production dans un vrai contexte métier. »</p>
    </div>
    <div class="block take">
      <span class="label">Mon take</span>
      <p>Le parallèle <strong>NestJS ↔ Spring</strong> est ta meilleure arme. Dis-le franco : « NestJS est littéralement inspiré de Spring — DI, modules, décorateurs. Le mental model, je l'ai déjà. » C'est vrai et ça désamorce le doute sur ton « manque » de Java.</p>
    </div>
    <div class="divider"></div>
  </section>

  <!-- 03 -->
  <section id="archi">
    <div class="sec-head"><span class="sec-num">03</span><h2>Questions archi — sans être dogmatique</h2></div>
    <p class="sub">But : montrer que tu sais <strong>quand ne PAS</strong> sur-architecturer.</p>

    <p class="body"><strong>« Comment tu structures une appli ? »</strong></p>
    <div class="block say">
      <span class="label">À dire</span>
      <p>« Je pars simple : les couches classiques controller/service/repository suffisent pour l'immense majorité des cas. J'isole la logique métier surtout pour pouvoir la tester vite et sans base de données. Je n'ajoute un pattern ou une abstraction que le jour où un test ou une contrainte métier réelle me le demande — jamais par principe esthétique. »</p>
    </div>

    <p class="body" style="margin-top:16px"><strong>« Tu connais la Clean Architecture / l'hexagonal ? »</strong></p>
    <div class="block say">
      <span class="label">À dire</span>
      <p>« Oui, et je m'en sers avec parcimonie. L'idée que je retiens surtout, c'est la testabilité : pouvoir tester ma logique métier en isolation, avec des implémentations in-memory à la place de la vraie infra. Mais sur un CRUD ou une IHM, je ne vais pas sortir l'artillerie hexagonale — ce serait du <em>coût sans bénéfice</em>. »</p>
    </div>
    <div class="block take">
      <span class="label">Mon take</span>
      <p>Piège inversé : la plupart des juniors sur-vendent la Clean Archi. Toi tu montres du <strong>discernement</strong> — exactement ce qu'une équipe banque veut entendre. Mot magique : « coût sans bénéfice ». Tu penses en ingénieur, pas en fan.</p>
    </div>
    <div class="divider"></div>
  </section>

  <!-- 04 -->
  <section id="questions">
    <div class="sec-head"><span class="sec-num">04</span><h2>Les questions à LEUR poser</h2></div>
    <p class="sub">Énorme différenciateur. Prépares-en 4-5, sors-en 2-3.</p>
    <ul class="ask">
      <li><span class="q">« L'IHM, c'est quel front ? Angular, React, ou du plus historique ? »</span><span class="why">→ tu penses stack réelle (et tu as Angular dans les mains)</span></li>
      <li><span class="q">« Comment vous testez ? Culture de tests automatisés, CI/CD dans l'équipe ? »</span><span class="why">→ tu ramènes le terrain sur ta force DevOps</span></li>
      <li><span class="q">« Le datawarehouse compta/risque, c'est de la donnée réglementaire ? Des enjeux de conformité qui cadrent ce que l'IHM affiche ? »</span><span class="why">→ curiosité métier rare chez un junior · ton money move</span></li>
      <li><span class="q">« Sur SRS Vision, vous êtes en création d'un nouveau module ou en reprise d'existant ? »</span><span class="why">→ tu anticipes la réalité du legacy</span></li>
      <li><span class="q">« Comment vous accompagnez un junior sur les premiers mois ? »</span><span class="why">→ signal de coachabilité</span></li>
    </ul>
    <div class="block take">
      <span class="label">Mon take</span>
      <p>La question conformité/réglementaire, personne ne l'attend d'un junior. Montrer que le code sert un enjeu de contrôle et de risque te fait passer de « codeur » à « futur ingénieur qui comprend le métier ».</p>
    </div>
    <div class="divider"></div>
  </section>

  <!-- 05 -->
  <section id="metier">
    <div class="sec-head"><span class="sec-num">05</span><h2>Le domaine métier — parle leur langue</h2></div>
    <p class="sub">Contexte : IHM = interface entre le datawarehouse Compta/Risque et les utilisateurs.</p>
    <div class="block say">
      <span class="label">À dire</span>
      <p>« Si je comprends bien, il y a une grosse base analytique côté compta/risque, et l'enjeu c'est de rendre cette donnée exploitable et lisible pour des utilisateurs métier via une interface. Donc les vrais défis, c'est probablement la performance des requêtes sur de gros volumes, la justesse des chiffres affichés, et l'ergonomie pour des utilisateurs non-techniques. »</p>
    </div>
    <div class="block trap">
      <span class="label">Ne bluffe pas</span>
      <p><strong>SRS Vision</strong> = projet interne BNP, sens exact non confirmé (probablement lié au reporting réglementaire/risque). Ne prétends pas savoir — <strong>pose la question</strong>. Compta/Risque = les fonctions qui mesurent l'exposition de la banque et produisent les états financiers/réglementaires.</p>
    </div>
    <div class="divider"></div>
  </section>

  <!-- 06 -->
  <section id="devops">
    <div class="sec-head"><span class="sec-num">06</span><h2>Ton atout sous-exploité : le DevOps</h2></div>
    <p class="sub">Tu formes sur Docker/CI/CD/K8s. Sur un poste junior, c'est une compétence de senior.</p>
    <div class="block say">
      <span class="label">À dire (au bon moment)</span>
      <p>« J'ai une vraie culture CI/CD — je forme même dessus. Donc au-delà d'écrire la feature, je sais penser build, tests automatisés en pipeline, conteneurisation. Pour une équipe, ça veut dire un junior qui n'a pas besoin qu'on lui tienne la main sur la partie livraison. »</p>
    </div>
    <div class="block take">
      <span class="label">Mon take</span>
      <p>Peut-être ton argument le plus sous-coté. Garde-le en réserve, sors-le au bon moment — ça repositionne toute la perception de ton niveau.</p>
    </div>
    <div class="divider"></div>
  </section>

  <!-- 07 -->
  <section id="behavioral">
    <div class="sec-head"><span class="sec-num">07</span><h2>Behavioral — classiques banque</h2></div>
    <ul class="ask">
      <li><span class="q">« Un désaccord technique avec un collègue ? »</span><span class="why">→ tu argumentes puis tu <em>disagree &amp; commit</em>. Esprit d'équipe > avoir raison.</span></li>
      <li><span class="q">« Ta plus grande faiblesse ? »</span><span class="why">→ « Mon Java de production est moins rodé que mon TypeScript — mais les fondamentaux sont là et je monte vite, la preuve j'enseigne des stacks que je dois maîtriser à fond. » Faiblesse réelle + preuve que tu la compenses.</span></li>
      <li><span class="q">« Pourquoi la banque ? »</span><span class="why">→ rigueur, exigence de fiabilité, données à fort enjeu, envie de bosser sur des systèmes qui comptent.</span></li>
    </ul>
    <div class="divider"></div>
  </section>

  <!-- 08 -->
  <section id="patterns">
    <div class="sec-head"><span class="sec-num">08</span><h2>Design patterns</h2></div>
    <p class="sub">À quoi ça sert + où Spring l'utilise. Déplie pour réviser.</p>

    <div class="block take">
      <span class="label">Mon take · la bonne réponse</span>
      <p>Ne récite pas le catalogue GoF. Si on te demande « tu connais des patterns ? » :</p>
      <p style="margin-top:8px">« Oui, mais je m'en méfie autant que je les utilise. Je n'introduis un pattern que quand un besoin réel le justifie — sinon c'est de la complexité gratuite. Ceux que j'utilise le plus : Strategy quand j'ai de la vraie variabilité d'algo, Builder pour les objets complexes, et l'injection de dépendances au quotidien via Spring. »</p>
    </div>

    <details>
      <summary>Les 10 à connaître</summary>
      <div class="content">
        <div class="item"><div class="name">Singleton</div><div class="desc">Une seule instance partagée dans toute l'appli.</div><div class="spring">→ Les beans Spring sont des singletons par défaut.</div></div>
        <div class="item"><div class="name">Factory / Factory Method</div><div class="desc">Déléguer la création d'objets à une méthode dédiée plutôt que <code>new</code> partout. Utile quand la création est complexe ou conditionnelle.</div><div class="spring">→ Le conteneur IoC est une immense factory de beans.</div></div>
        <div class="item"><div class="name">Strategy</div><div class="desc">Plusieurs algorithmes interchangeables derrière une même interface, choisis à l'exécution. Ex : plusieurs modes de calcul de risque/frais.</div><div class="spring">→ Tu injectes la bonne implémentation selon le contexte.</div></div>
        <div class="item"><div class="name">Builder</div><div class="desc">Construire un objet complexe étape par étape, surtout avec beaucoup de champs optionnels. Évite les constructeurs à 12 paramètres.</div><div class="spring">→ Tu le connais via Lombok <code>@Builder</code>.</div></div>
        <div class="item"><div class="name">Adapter</div><div class="desc">Faire dialoguer deux interfaces incompatibles en les « traduisant ».</div><div class="spring">→ L'idée derrière tes ports/adapters.</div></div>
        <div class="item"><div class="name">Observer</div><div class="desc">Un objet notifie automatiquement ses abonnés d'un changement.</div><div class="spring">→ Spring : <code>ApplicationEvent</code> / <code>@EventListener</code>.</div></div>
        <div class="item"><div class="name">Dependency Injection</div><div class="desc">Fournir les dépendances de l'extérieur au lieu de les créer soi-même. Si tu n'en maîtrises qu'un, c'est celui-là.</div><div class="spring">→ Le cœur de Spring.</div></div>
        <div class="item"><div class="name">Proxy</div><div class="desc">Un objet intermédiaire qui contrôle l'accès à un autre.</div><div class="spring">→ <code>@Transactional</code> et l'AOP passent par des proxies. Bon point bonus.</div></div>
        <div class="item"><div class="name">Template Method</div><div class="desc">Squelette d'un algo dans une méthode, les sous-classes remplissent certaines étapes.</div><div class="spring">→ Les classes <code>...Template</code> (<code>JdbcTemplate</code>, <code>RestTemplate</code>).</div></div>
        <div class="item"><div class="name">Repository</div><div class="desc">Abstraire l'accès aux données derrière une interface orientée « collection d'objets ».</div><div class="spring">→ Littéralement <code>JpaRepository</code>.</div></div>
      </div>
    </details>

    <p class="body" style="margin-top:6px"><strong>Exemple Strategy à réciter :</strong></p>
<pre>interface CalculStrategy { BigDecimal calculer(Montant m); }
// StandardCalcul, PremiumCalcul... injectées selon le contexte</pre>
    <div class="divider"></div>
  </section>

  <!-- 09 -->
  <section id="annotations">
    <div class="sec-head"><span class="sec-num">09</span><h2>Annotations à connaître</h2></div>
    <p class="sub">Les <span class="pill">gras</span> = on peut te demander de les expliquer, pas juste de citer.</p>

    <details open>
      <summary>Stéréotypes / composants</summary>
      <div class="content">
        <div class="fam">Le conteneur</div>
        <div class="item"><div class="name">@Component</div><div class="desc">bean générique géré par Spring</div></div>
        <div class="item"><div class="name">@Service</div><div class="desc">bean de couche métier (sémantique)</div></div>
        <div class="item"><div class="name">@Repository</div><div class="desc">bean d'accès données <strong>+ traduction des exceptions de persistance</strong></div></div>
        <div class="item"><div class="name">@Controller / @RestController</div><div class="desc">bean web · REST = + <code>@ResponseBody</code></div></div>
        <div class="item"><div class="name">@Configuration + @Bean</div><div class="desc">classe de config qui déclare des beans manuellement</div></div>
        <div class="item"><div class="name">@Autowired</div><div class="desc">injection — mais tu préfères l'injection par constructeur, souvent sans annotation</div></div>
      </div>
    </details>

    <details>
      <summary>Web / REST (Spring MVC)</summary>
      <div class="content">
        <div class="item"><div class="name">@RequestMapping</div><div class="desc">mapping URL général (à la classe)</div></div>
        <div class="item"><div class="name">@GetMapping / @PostMapping / @PutMapping / @DeleteMapping</div><div class="desc">raccourcis par verbe HTTP</div></div>
        <div class="item"><div class="name">@PathVariable</div><div class="desc">extraire une variable de l'URL (<code>/users/{id}</code>)</div></div>
        <div class="item"><div class="name">@RequestParam</div><div class="desc">extraire un paramètre de query (<code>?statut=actif</code>)</div></div>
        <div class="item"><div class="name">@RequestBody</div><div class="desc">désérialiser le corps JSON en objet Java</div></div>
        <div class="item"><div class="name">@ResponseStatus</div><div class="desc">forcer un code HTTP de retour</div></div>
      </div>
    </details>

    <details>
      <summary>JPA / Persistence</summary>
      <div class="content">
        <div class="item"><div class="name">@Entity</div><div class="desc">classe mappée sur une table</div></div>
        <div class="item"><div class="name">@Id + @GeneratedValue</div><div class="desc">clé primaire + stratégie de génération</div></div>
        <div class="item"><div class="name">@Column / @Table</div><div class="desc">mapping colonne / table</div></div>
        <div class="item"><div class="name">@OneToMany / @ManyToOne / @OneToOne</div><div class="desc">relations — sache expliquer le sens + lazy/eager</div></div>
        <div class="item"><div class="name">@JoinColumn</div><div class="desc">la colonne de clé étrangère</div></div>
        <div class="item"><div class="name">@Query</div><div class="desc">écrire ta requête JPQL ou SQL natif toi-même</div></div>
      </div>
    </details>

    <details>
      <summary>Spring Boot / config</summary>
      <div class="content">
        <div class="item"><div class="name">@SpringBootApplication</div><div class="desc">annotation racine = <code>@Configuration</code> + <code>@EnableAutoConfiguration</code> + <code>@ComponentScan</code>. Sache la décomposer.</div></div>
        <div class="item"><div class="name">@Value</div><div class="desc">injecter une valeur de config (<code>application.properties</code>)</div></div>
        <div class="item"><div class="name">@Profile</div><div class="desc">activer un bean selon l'environnement (dev/prod)</div></div>
      </div>
    </details>

    <details>
      <summary>Transactions</summary>
      <div class="content">
        <div class="item"><div class="name">@Transactional</div><div class="desc">rend une méthode transactionnelle (commit/rollback auto). Marche <strong>via un proxy</strong> (lien avec le pattern Proxy). Par défaut rollback sur exceptions <em>unchecked</em> seulement.</div></div>
      </div>
    </details>

    <details>
      <summary>Tests</summary>
      <div class="content">
        <div class="item"><div class="name">@SpringBootTest</div><div class="desc">charge le contexte Spring complet (test d'intégration)</div></div>
        <div class="item"><div class="name">@WebMvcTest / @DataJpaTest</div><div class="desc">charge seulement une tranche (web / JPA), plus rapide</div></div>
        <div class="item"><div class="name">@Test</div><div class="desc">(JUnit) marque une méthode de test</div></div>
        <div class="item"><div class="name">@Mock / @InjectMocks</div><div class="desc">(Mockito) créer et injecter des mocks</div></div>
        <div class="item"><div class="name">@BeforeEach / @AfterEach</div><div class="desc">setup / teardown avant chaque test</div></div>
      </div>
    </details>

    <details>
      <summary>Lombok (si l'équipe l'utilise — très courant)</summary>
      <div class="content">
        <div class="item" style="border-top:none">
          <span class="pill">@Getter</span><span class="pill">@Setter</span><span class="pill">@Data</span><span class="pill">@Builder</span><span class="pill">@AllArgsConstructor</span><span class="pill">@NoArgsConstructor</span><span class="pill">@RequiredArgsConstructor</span>
          <div class="note"><code>@RequiredArgsConstructor</code> est top pour l'injection par constructeur : il génère le constructeur avec tes champs <code>final</code>.</div>
        </div>
      </div>
    </details>
    <div class="divider"></div>
  </section>

  <!-- 10 -->
  <section id="pieges">
    <div class="sec-head"><span class="sec-num">10</span><h2>Les 3 pièges annotations + le combo</h2></div>
    <div class="block trap">
      <span class="label">Piège 1</span>
      <p><strong>« Décompose @SpringBootApplication »</strong> → <code>@Configuration</code> + <code>@EnableAutoConfiguration</code> + <code>@ComponentScan</code>.</p>
    </div>
    <div class="block trap">
      <span class="label">Piège 2</span>
      <p><strong>« Différence @Component / @Service / @Repository ? »</strong> → techniquement tous des beans, la différence est sémantique (+ <code>@Repository</code> traduit les exceptions).</p>
    </div>
    <div class="block trap">
      <span class="label">Piège 3</span>
      <p><strong>« Comment marche @Transactional ? »</strong> → proxy autour du bean, ouvre une transaction avant la méthode, commit après, rollback si exception unchecked.</p>
    </div>
    <div class="block take">
      <span class="label">Le combo qui claque</span>
      <p>Relie un pattern à une annotation : « <code>@Transactional</code> c'est un Proxy », ou « le conteneur Spring c'est une grosse Factory de Singletons ». Tu ne récites pas des listes — tu comprends la mécanique dessous.</p>
    </div>
  </section>

</div>

<footer class="wrap">
  Fiche de prep — à relire ce soir. Bonne chance demain 10h.
</footer>

</body>
</html>
