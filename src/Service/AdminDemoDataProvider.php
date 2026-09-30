<?php

declare(strict_types=1);

namespace App\Service;

final class AdminDemoDataProvider
{
    /** @return array<string, mixed> */
    public function dashboard(): array
    {
        return [
            'stats' => [
                ['label' => 'Utilisateurs en ligne', 'value' => '18', 'trend' => 'En direct', 'tone' => 'live', 'icon' => 'activity'],
                ['label' => 'Missions déposées', 'value' => '24', 'trend' => '+18 % ce mois', 'tone' => 'positive', 'icon' => 'briefcase'],
                ['label' => 'Profils reçus', 'value' => '67', 'trend' => '+12 % ce mois', 'tone' => 'positive', 'icon' => 'people'],
                ['label' => 'Taux de conversion', 'value' => '8,4 %', 'trend' => '+1,2 point', 'tone' => 'positive', 'icon' => 'chart'],
            ],
            'traffic' => [
                ['day' => 'Lun', 'value' => 46, 'visits' => 184],
                ['day' => 'Mar', 'value' => 62, 'visits' => 248],
                ['day' => 'Mer', 'value' => 54, 'visits' => 217],
                ['day' => 'Jeu', 'value' => 78, 'visits' => 312],
                ['day' => 'Ven', 'value' => 91, 'visits' => 364],
                ['day' => 'Sam', 'value' => 57, 'visits' => 229],
                ['day' => 'Dim', 'value' => 69, 'visits' => 276],
            ],
            'quickStats' => [
                ['label' => 'Demandes à traiter', 'value' => '9', 'detail' => '3 prioritaires'],
                ['label' => 'Temps de réponse moyen', 'value' => '2 h 18', 'detail' => 'Objectif : moins de 4 h'],
                ['label' => 'Pages publiées', 'value' => '4 / 4', 'detail' => 'Contenu à jour'],
            ],
            'recentSubmissions' => array_slice($this->submissions(), 0, 5),
            'popularPages' => [
                ['name' => 'Accueil', 'views' => '2 841', 'share' => 88],
                ['name' => 'Nos services', 'views' => '1 936', 'share' => 63],
                ['name' => 'À propos', 'views' => '1 127', 'share' => 39],
                ['name' => 'Contact', 'views' => '864', 'share' => 28],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public function pages(): array
    {
        return [
            $this->pageDefinition('home'),
            $this->pageDefinition('services'),
            $this->pageDefinition('about'),
            $this->pageDefinition('contact'),
        ];
    }

    /** @return list<array<string, mixed>> */
    public function components(): array
    {
        return [
            $this->componentDefinition('header'),
            $this->componentDefinition('footer'),
        ];
    }

    /** @return array<string, mixed>|null */
    public function page(string $slug): ?array
    {
        if (in_array($slug, ['home', 'services', 'about', 'contact'], true)) {
            return $this->pageDefinition($slug);
        }

        if (in_array($slug, ['header', 'footer'], true)) {
            return $this->componentDefinition($slug);
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public function posts(): array
    {
        return [
            ['title' => 'Les enjeux AML/KYC en 2026', 'category' => 'Conformité', 'status' => 'Publié', 'date' => '12 sept. 2026', 'views' => '684'],
            ['title' => 'Renforcer son contrôle interne', 'category' => 'Gouvernance', 'status' => 'Publié', 'date' => '4 sept. 2026', 'views' => '512'],
            ['title' => 'IA et métiers réglementés', 'category' => 'Digital', 'status' => 'Brouillon', 'date' => 'Aujourd’hui', 'views' => '—'],
            ['title' => 'Externaliser une fonction compliance', 'category' => 'Conseil', 'status' => 'Planifié', 'date' => '22 sept. 2026', 'views' => '—'],
        ];
    }

    /** @return list<array<string, mixed>> */
    public function submissions(): array
    {
        return [
            ['type' => 'Mission', 'name' => 'Compliance Officer AML/KYC', 'contact' => 'Banque Horizon', 'date' => 'Il y a 12 min', 'status' => 'Nouveau', 'priority' => true],
            ['type' => 'Profil', 'name' => 'Sophie Lambert', 'contact' => 'Senior Risk Manager', 'date' => 'Il y a 34 min', 'status' => 'À qualifier', 'priority' => false],
            ['type' => 'Mission', 'name' => 'Renfort Fund Administration', 'contact' => 'Northbridge AM', 'date' => 'Il y a 1 h', 'status' => 'En cours', 'priority' => false],
            ['type' => 'Profil', 'name' => 'Thomas Weber', 'contact' => 'IT Business Analyst', 'date' => 'Il y a 2 h', 'status' => 'Nouveau', 'priority' => true],
            ['type' => 'Profil', 'name' => 'Claire Dubois', 'contact' => 'Compliance Officer', 'date' => 'Hier, 17:42', 'status' => 'Contacté', 'priority' => false],
            ['type' => 'Mission', 'name' => 'Audit réglementaire', 'contact' => 'FinLux Partners', 'date' => 'Hier, 15:18', 'status' => 'Contacté', 'priority' => false],
        ];
    }

    /** @return array<string, mixed> */
    private function pageDefinition(string $slug): array
    {
        $pages = [
            'home' => [
                'slug' => 'home', 'name' => 'Accueil', 'route' => 'app_home', 'path' => '/', 'icon' => 'home',
                'description' => 'Page principale, radar, expertises et présentation du cabinet.', 'sectionsCount' => 10, 'updated' => 'Aujourd’hui, 11:42',
                'sections' => [
                    $this->section('hero', 'En-tête d’accueil', '.hero', [
                        $this->textField('Badge de confiance', 'Un réseau qualifié au service des acteurs financiers', '.hero .hero-trust-pill'),
                        $this->textField('Titre — première ligne', 'Consultants spécialisés', '.hero h1 span:first-child'),
                        $this->textField('Titre — ligne accentuée', 'pour le monde financier', '.hero h1 span.accent'),
                        $this->textField('Introduction', 'Nous accompagnons les entreprises du secteur financier au Luxembourg en leur proposant des consultants externes spécialisés.', '.hero .lead', true),
                        $this->textField('Preuve 1 — chiffre', '+100', '.hero-reassurance > span:nth-child(1) strong'),
                        $this->textField('Preuve 1 — libellé', 'clients satisfaits', '.hero-reassurance > span:nth-child(1) .hero-reassurance-label'),
                        $this->textField('Preuve 2 — libellé', 'Profils présentés sous', '.hero-reassurance > span:nth-child(2) .hero-reassurance-label'),
                        $this->textField('Preuve 2 — délai', '48h', '.hero-reassurance > span:nth-child(2) strong'),
                        $this->textField('Preuve 3 — libellé', 'Expertise', '.hero-reassurance > span:nth-child(3) .hero-reassurance-label'),
                        $this->textField('Preuve 3 — expertise', 'finance & conformité', '.hero-reassurance > span:nth-child(3) strong'),
                        $this->buttonField('Bouton principal', 'Déposer une mission', '.hero .button-primary'),
                        $this->buttonField('Bouton secondaire', 'Découvrir nos expertises', '.hero .button-quiet'),
                    ]),
                    $this->section('radar', 'Radar des experts', '.radar-section', [
                        $this->textField('Sur-titre', 'Notre réseau d’experts', '.radar-section .eyebrow'),
                        $this->textField('Titre', 'Les bons experts. Au bon moment.', '.radar-section h2'),
                        $this->textField('Description', 'Nous qualifions votre besoin, activons notre réseau et vous présentons des consultants spécialisés, disponibles et adaptés à votre environnement.', '.radar-section-intro p', true),
                        $this->buttonField('Bouton', 'Confier une mission', '.radar-section-intro .button'),
                        $this->textField('Légende du radar', 'RADAR D’EXPERTISE · RÉSEAU QUALIFIÉ', '.radar-section .stage-caption'),
                        $this->textField('Expertise radar 1', 'Compliance', '.radar-section .network-label.node-1'),
                        $this->textField('Expertise radar 2', 'Finance', '.radar-section .network-label.node-2'),
                        $this->textField('Expertise radar 3', 'Risk management', '.radar-section .network-label.node-3'),
                        $this->textField('Expertise radar 4', 'Transformation IT', '.radar-section .network-label.node-4'),
                        $this->textField('Expertise radar 5', 'Formation', '.radar-section .network-label.node-5'),
                        $this->textField('Mission exemple', 'Mission · Compliance AML', '.matching-dashboard-bar p'),
                        $this->textField('Mission — statut', 'MISSION ACTIVE', '.matching-dashboard-title > span'),
                        $this->textField('Mission — fonction', 'Compliance Officer', '.matching-dashboard-title > strong'),
                        $this->textField('Mission — contexte', 'Luxembourg · Fonds d’investissement', '.matching-dashboard-title > small'),
                        $this->textField('Indicateur 1 — valeur', '48h', '.matching-dashboard-metrics article:nth-child(1) strong'),
                        $this->textField('Indicateur 1 — libellé', 'Délai moyen', '.matching-dashboard-metrics article:nth-child(1) span'),
                        $this->textField('Indicateur 2 — valeur', '3', '.matching-dashboard-metrics article:nth-child(2) strong'),
                        $this->textField('Indicateur 2 — libellé', 'Profils ciblés', '.matching-dashboard-metrics article:nth-child(2) span'),
                        $this->textField('Indicateur 3 — valeur', '100%', '.matching-dashboard-metrics article:nth-child(3) strong'),
                        $this->textField('Indicateur 3 — libellé', 'Profils validés', '.matching-dashboard-metrics article:nth-child(3) span'),
                        $this->textField('Atout 1 — titre', 'Un réseau ciblé et qualifié', '.radar-section-captions article:first-child h3'),
                        $this->textField('Atout 1 — description', 'Finance, conformité, risques et transformation : nous activons les expertises réellement utiles à votre mission.', '.radar-section-captions article:first-child p', true),
                        $this->textField('Atout 2 — titre', 'Une sélection rapide et transparente', '.radar-section-captions article:nth-child(2) h3'),
                        $this->textField('Atout 2 — description', 'Chaque profil est vérifié, disponible et présenté avec les éléments nécessaires pour décider sereinement.', '.radar-section-captions article:nth-child(2) p', true),
                    ]),
                    $this->section('ecosystem', 'Secteurs accompagnés', '.ecosystem', [
                        $this->textField('Sur-titre', 'Nos expertises', '.ecosystem .eyebrow'),
                        $this->textField('Titre', 'Des compétences adaptées à vos enjeux réglementaires et financiers', '.ecosystem h2'),
                        $this->textField('Description', 'Nous intervenons dans les métiers clés du secteur financier pour répondre à vos besoins ponctuels ou stratégiques.', '.ecosystem-heading > p', true),
                    ], $this->carousel(4800, '.carousel-viewport', '.carousel-track', [
                        $this->carouselCard('Banques privées', '.ecosystem .sector-card:nth-child(1)', [
                            $this->textField('Titre', 'Banques privées', '.ecosystem .sector-card:nth-child(1) h3'),
                            $this->textField('Accroche', 'La confiance, à chaque étape.', '.ecosystem .sector-card:nth-child(1) .sector-tagline'),
                            $this->textField('Description', 'Conformité, connaissance client et expertise opérationnelle au service de vos équipes.', '.ecosystem .sector-card:nth-child(1) .sector-description', true),
                            $this->buttonField('Lien', 'Explorer nos expertises', '.ecosystem .sector-card:nth-child(1) .text-link'),
                        ]),
                        $this->carouselCard('Fonds d’investissement', '.ecosystem .sector-card:nth-child(2)', [
                            $this->textField('Titre', 'Fonds d’investissement', '.ecosystem .sector-card:nth-child(2) h3'),
                            $this->textField('Accroche', 'De l’exigence à la précision.', '.ecosystem .sector-card:nth-child(2) .sector-tagline'),
                            $this->textField('Description', 'Administration de fonds, reporting et gouvernance : des compétences au cœur de vos opérations.', '.ecosystem .sector-card:nth-child(2) .sector-description', true),
                            $this->buttonField('Lien', 'Explorer nos expertises', '.ecosystem .sector-card:nth-child(2) .text-link'),
                        ]),
                        $this->carouselCard('Assurances', '.ecosystem .sector-card:nth-child(3)', [
                            $this->textField('Titre', 'Assurances', '.ecosystem .sector-card:nth-child(3) h3'),
                            $this->textField('Accroche', 'Anticiper pour mieux protéger.', '.ecosystem .sector-card:nth-child(3) .sector-tagline'),
                            $this->textField('Description', 'Risques, contrôle interne et conformité : une expertise attentive à votre environnement.', '.ecosystem .sector-card:nth-child(3) .sector-description', true),
                            $this->buttonField('Lien', 'Explorer nos expertises', '.ecosystem .sector-card:nth-child(3) .text-link'),
                        ]),
                        $this->carouselCard('Fiduciaires & audit', '.ecosystem .sector-card:nth-child(4)', [
                            $this->textField('Titre', 'Fiduciaires & audit', '.ecosystem .sector-card:nth-child(4) h3'),
                            $this->textField('Accroche', 'Un regard juste. Un renfort ciblé.', '.ecosystem .sector-card:nth-child(4) .sector-tagline'),
                            $this->textField('Description', 'Des consultants pour accompagner vos missions comptables, financières et de contrôle.', '.ecosystem .sector-card:nth-child(4) .sector-description', true),
                            $this->buttonField('Lien', 'Explorer nos expertises', '.ecosystem .sector-card:nth-child(4) .text-link'),
                        ]),
                        $this->carouselCard('Fintechs', '.ecosystem .sector-card:nth-child(5)', [
                            $this->textField('Titre', 'Fintechs', '.ecosystem .sector-card:nth-child(5) h3'),
                            $this->textField('Accroche', 'L’innovation, bien accompagnée.', '.ecosystem .sector-card:nth-child(5) .sector-tagline'),
                            $this->textField('Description', 'Reliez vos projets numériques aux compétences métier, IT et réglementaires qui les font avancer.', '.ecosystem .sector-card:nth-child(5) .sector-description', true),
                            $this->buttonField('Lien', 'Explorer nos expertises', '.ecosystem .sector-card:nth-child(5) .text-link'),
                        ]),
                    ])),
                    $this->section('expertises', 'Nos expertises', '[data-section="expertises"]', [
                        $this->textField('Sur-titre', 'Notre expertise', '[data-section="expertises"] .eyebrow'),
                        $this->textField('Titre', 'Une expertise reconnue dans les secteurs financiers.', '[data-section="expertises"] h2'),
                        $this->buttonField('Lien de section', 'Toutes nos expertises', '[data-section="expertises"] .section-heading .text-link'),
                    ], $this->carousel(5600, '.expertise-viewport', '.expertise-grid', [
                        $this->carouselCard('Compliance & réglementation', '[data-section="expertises"] .expertise-card:nth-child(1)', [
                            $this->textField('Visuel — titre', 'Votre dispositif de conformité', '[data-section="expertises"] .expertise-card:nth-child(1) .mini-title'),
                            $this->textField('Visuel — ligne 1', 'Connaissance client', '[data-section="expertises"] .expertise-card:nth-child(1) .mini-row:nth-child(2)'),
                            $this->textField('Visuel — ligne 2', 'Analyse des risques', '[data-section="expertises"] .expertise-card:nth-child(1) .mini-row:nth-child(3)'),
                            $this->textField('Visuel — ligne 3', 'Contrôle & suivi', '[data-section="expertises"] .expertise-card:nth-child(1) .mini-row:nth-child(4)'),
                            $this->textField('Visuel — badge', 'L’expertise à chaque étape', '[data-section="expertises"] .expertise-card:nth-child(1) .visual-badge'),
                            $this->textField('Titre', 'Compliance & réglementation', '[data-section="expertises"] .expertise-card:nth-child(1) h3'),
                            $this->textField('Description', 'Nous vous accompagnons dans la mise en conformité, le renforcement des dispositifs de contrôle et l’anticipation des évolutions réglementaires.', '[data-section="expertises"] .expertise-card:nth-child(1) > p', true),
                            $this->buttonField('Lien', 'Renforcer ma conformité', '[data-section="expertises"] .expertise-card:nth-child(1) .text-link'),
                        ]),
                        $this->carouselCard('Finance & fonds', '[data-section="expertises"] .expertise-card:nth-child(2)', [
                            $this->textField('Visuel — titre', 'Finance & opérations', '[data-section="expertises"] .expertise-card:nth-child(2) .mini-title'),
                            $this->textField('Visuel — tag', 'Fonds', '[data-section="expertises"] .expertise-card:nth-child(2) .mini-top .pill'),
                            $this->textField('Visuel — badge', 'De la précision à la décision', '[data-section="expertises"] .expertise-card:nth-child(2) .visual-badge'),
                            $this->textField('Titre', 'Finance & fonds d’investissement', '[data-section="expertises"] .expertise-card:nth-child(2) h3'),
                            $this->textField('Description', 'Nous accompagnons les sociétés de gestion et administrateurs de fonds dans leurs fonctions opérationnelles, financières et réglementaires.', '[data-section="expertises"] .expertise-card:nth-child(2) > p', true),
                            $this->buttonField('Lien', 'Accompagner mes opérations', '[data-section="expertises"] .expertise-card:nth-child(2) .text-link'),
                        ]),
                        $this->carouselCard('Risques & gouvernance', '[data-section="expertises"] .expertise-card:nth-child(3)', [
                            $this->textField('Visuel — tag 1', 'Anticiper', '[data-section="expertises"] .expertise-card:nth-child(3) span.risk-tag:first-of-type'),
                            $this->textField('Visuel — tag 2', 'Piloter', '[data-section="expertises"] .expertise-card:nth-child(3) span.risk-tag:last-of-type'),
                            $this->textField('Titre', 'Risques & gouvernance', '[data-section="expertises"] .expertise-card:nth-child(3) h3'),
                            $this->textField('Description', 'Nous renforçons vos dispositifs de contrôle interne, de gestion des risques et de gouvernance avec des experts immédiatement opérationnels.', '[data-section="expertises"] .expertise-card:nth-child(3) > p', true),
                            $this->buttonField('Lien', 'Structurer mes dispositifs', '[data-section="expertises"] .expertise-card:nth-child(3) .text-link'),
                        ]),
                        $this->carouselCard('Transformation & IT', '[data-section="expertises"] .expertise-card:nth-child(4)', [
                            $this->textField('Visuel — badge', 'La transformation en mouvement', '[data-section="expertises"] .expertise-card:nth-child(4) .visual-badge'),
                            $this->textField('Titre', 'Transformation & IT', '[data-section="expertises"] .expertise-card:nth-child(4) h3'),
                            $this->textField('Description', 'Nous vous aidons à structurer, piloter et sécuriser vos projets de transformation digitale dans un environnement financier exigeant.', '[data-section="expertises"] .expertise-card:nth-child(4) > p', true),
                            $this->buttonField('Lien', 'Faire avancer mes projets', '[data-section="expertises"] .expertise-card:nth-child(4) .text-link'),
                        ]),
                    ])),
                    $this->section('approche', 'Notre approche', '[data-section="approche"]', [
                        $this->textField('Sur-titre', 'Notre approche', '[data-section="approche"] .eyebrow'),
                        $this->textField('Titre', 'Un accompagnement simple et efficace.', '[data-section="approche"] h2'),
                        $this->textField('Description', 'De la compréhension du besoin au suivi de la mission, chaque étape est structurée pour vous faire gagner du temps.', '[data-section="approche"] .approach-heading > p', true),
                        $this->textField('Visuel — en-tête', 'VOTRE PROJET · ÉTAPE PAR ÉTAPE', '[data-section="approche"] .approach-canvas-top > span:first-child'),
                        $this->textField('Visuel 1 — statut', 'BESOIN QUALIFIÉ', '[data-section="approche"] .match-brief-card > span'),
                        $this->textField('Visuel 1 — fonction', 'Compliance Officer', '[data-section="approche"] .match-brief-card > strong'),
                        $this->textField('Visuel 1 — contexte', 'Luxembourg · Fonds d’investissement', '[data-section="approche"] .match-brief-card > small'),
                        $this->textField('Visuel 1 — résultat', '3 profils', '[data-section="approche"] .match-result-chip strong'),
                        $this->textField('Visuel 1 — délai', 'présentés sous 48h', '[data-section="approche"] .match-result-chip span'),
                        $this->textField('Visuel 2 — titre', 'COUVERTURE DU BESOIN', '[data-section="approche"] .expertise-score-card span'),
                        $this->textField('Visuel 2 — score', '94%', '[data-section="approche"] .expertise-score-card strong'),
                        $this->textField('Visuel 2 — description', 'Expertises disponibles et immédiatement mobilisables', '[data-section="approche"] .expertise-score-card p', true),
                        $this->textField('Visuel 3 — statut', 'Profil validé', '[data-section="approche"] .process-tooltip > strong'),
                        $this->textField('Visuel 3 — étape 1', 'Besoin défini', '[data-section="approche"] .process-label-one p'),
                        $this->textField('Visuel 3 — étape 2', 'Profils reçus', '[data-section="approche"] .process-label-two p'),
                        $this->textField('Visuel 3 — étape 3', 'Consultant choisi', '[data-section="approche"] .process-label-three p'),
                        $this->textField('Visuel 3 — étape 4', 'Mission suivie', '[data-section="approche"] .process-label-four p'),
                        $this->textField('Étape 1 — titre', 'Mise en relations', '[data-section="approche"] .approach-tab:first-child h3'),
                        $this->textField('Étape 1 — description', 'Nous identifions des consultants hautement qualifiés et disponibles.', '[data-section="approche"] .approach-tab:first-child p', true),
                        $this->textField('Étape 2 — titre', 'Expertise secteur', '[data-section="approche"] .approach-tab:nth-child(2) h3'),
                        $this->textField('Étape 2 — description', 'Notre connaissance du marché luxembourgeois permet une sélection précise et immédiatement opérationnelle.', '[data-section="approche"] .approach-tab:nth-child(2) p', true),
                        $this->textField('Étape 3 — titre', 'Processus fluide', '[data-section="approche"] .approach-tab:nth-child(3) h3'),
                        $this->textField('Étape 3 — description', 'Un accompagnement simple et transparent, de la définition du besoin au suivi de la mission.', '[data-section="approche"] .approach-tab:nth-child(3) p', true),
                    ]),
                    $this->section('formation', 'Formation professionnelle', '[data-section="formation"]', [
                        $this->textField('Sur-titre', 'Formation professionnelle', '[data-section="formation"] .eyebrow'),
                        $this->textField('Titre', 'Formez vos équipes. Suivez les compétences. Gagnez en conformité.', '[data-section="formation"] h2'),
                        $this->textField('Description', 'Des formations conçues pour les professionnels du secteur financier au Luxembourg.', '[data-section="formation"] > div:first-child > p', true),
                        $this->buttonField('Bouton principal', 'Découvrir l’e-learning', '[data-section="formation"] .button'),
                        $this->buttonField('Lien secondaire', 'Nous contacter', '[data-section="formation"] .text-link'),
                        $this->textField('Aperçu — marque', 'Les Consultants', '[data-section="formation"] .learning-board-header .academy-name'),
                        $this->textField('Aperçu — gamme', 'Academy', '[data-section="formation"] .learning-board-header .accent'),
                        $this->textField('Aperçu — sur-titre', 'EXEMPLE DE PARCOURS', '[data-section="formation"] p.board-subtitle:first-of-type'),
                        $this->textField('Aperçu — titre', 'La conformité commence par la connaissance.', '[data-section="formation"] .learning-board h3'),
                        $this->textField('Aperçu — description', 'Des sujets concrets. Un parcours à votre rythme.', '[data-section="formation"] p.board-subtitle:nth-of-type(2)'),
                        $this->textField('Module 1', 'Comprendre les enjeux AML / CFT', '[data-section="formation"] div.lesson:nth-of-type(2) .lesson-title'),
                        $this->textField('Module 2', 'Identifier et évaluer les risques', '[data-section="formation"] div.lesson:nth-of-type(3) .lesson-title'),
                        $this->textField('Module 3', 'Mettre les acquis en pratique', '[data-section="formation"] div.lesson:nth-of-type(4) .lesson-title'),
                        $this->textField('Pied — format', 'Formation en ligne', '[data-section="formation"] .board-footer span:first-child'),
                        $this->textField('Pied — rythme', 'À votre rythme ↗', '[data-section="formation"] .board-footer span:nth-child(2)'),
                    ]),
                    $this->section('cabinet', 'Présentation du cabinet', '[data-section="cabinet"]', [
                        $this->textField('Sur-titre', 'À propos', '[data-section="cabinet"] .eyebrow'),
                        $this->textField('Titre', 'Experts en finance & conformité', '[data-section="cabinet"] h2'),
                        $this->textField('Description', 'Notre mission est simple : connecter les meilleurs consultants avec les acteurs clés du secteur financier.', '[data-section="cabinet"] h2 + p', true),
                        $this->textField('Description complémentaire', 'Grâce à notre expertise sectorielle pointue et à un réseau qualifié, nous vous aidons à renforcer vos équipes rapidement.', '[data-section="cabinet"] h2 + p + p', true),
                        $this->textField('Spécialités sur l’image', 'Compliance · Finance · Risk', '[data-section="cabinet"] .image-specialties'),
                        $this->textField('Signature sur l’image', 'Un partenaire de confiance pour vos missions critiques', '[data-section="cabinet"] .image-stamp'),
                        $this->buttonField('Lien', 'Rencontrer le cabinet', '[data-section="cabinet"] .text-link'),
                        $this->imageField('Visuel principal', 'cabinet-experts.webp', '[data-section="cabinet"] img'),
                    ]),
                    $this->section('faq', 'Questions fréquentes', '.faq-layout', [
                        $this->textField('Sur-titre', 'Foire aux questions', '.faq-layout .eyebrow'),
                        $this->textField('Titre', 'Nos réponses à vos questions', '.faq-layout h2'),
                        $this->textField('Introduction', 'Vous ne trouvez pas la réponse à votre question ? Contactez-nous.', '.faq-intro > p', true),
                        $this->buttonField('Lien', 'Poser une question', '.faq-intro .text-link'),
                        $this->textField('Question 1', 'Quel type de profils proposez-vous ?', '.faq-item:first-child summary'),
                        $this->textField('Réponse 1', 'Nous proposons des consultants externes expérimentés dans la finance, la conformité, les risques et la transformation digitale.', '.faq-item:first-child p', true),
                        $this->textField('Question 2', 'Sous quel délai recevrons-nous des candidatures ?', '.faq-item:nth-child(2) summary'),
                        $this->textField('Réponse 2', 'Nous nous engageons à vous présenter des profils adaptés sous 48 heures, avec un suivi personnalisé.', '.faq-item:nth-child(2) p', true),
                        $this->textField('Question 3', 'Quels sont vos domaines d’expertise ?', '.faq-item:nth-child(3) summary'),
                        $this->textField('Réponse 3', 'Nos domaines couvrent la conformité, l’AML/CFT, MIFID, DORA, MICA, la cybersécurité et le digital.', '.faq-item:nth-child(3) p', true),
                        $this->textField('Question 4', 'Les consultants sont-ils déjà disponibles ?', '.faq-item:nth-child(4) summary'),
                        $this->textField('Réponse 4', 'Oui, nos consultants sont déjà référencés et validés. Nous vérifions leur disponibilité avant toute présentation.', '.faq-item:nth-child(4) p', true),
                        $this->textField('Question 5', 'Travaillez-vous uniquement au Luxembourg ?', '.faq-item:nth-child(5) summary'),
                        $this->textField('Réponse 5', 'Non, nous intervenons aussi en Belgique et en Europe dans un environnement international.', '.faq-item:nth-child(5) p', true),
                    ]),
                    $this->section('resources', 'Actualités & conseils', '#ressources', [
                        $this->textField('Sur-titre', 'Regards sur nos métiers', '#ressources .eyebrow'),
                        $this->textField('Titre', 'Actualités & conseils du secteur', '#ressources h2'),
                        $this->textField('Description', 'Retrouvez les éclairages publiés par le cabinet.', '#ressources .section-heading > p', true),
                        $this->textField('Article 1 — catégorie', 'Conseil', '#ressources .insight-card:nth-child(1) .meta span'),
                        $this->textField('Article 1 — date', '5 août 2025', '#ressources .insight-card:nth-child(1) .meta time'),
                        $this->textField('Article 1 — titre', 'Freelances & Entreprises : une nouvelle manière de collaborer', '#ressources .insight-card:first-child h3'),
                        $this->buttonField('Article 1 — lien', 'Lire l’analyse', '#ressources .insight-card:nth-child(1) .text-link'),
                        $this->imageField('Article 1 — image', '01-freelances-featured.webp', '#ressources .insight-card:first-child img'),
                        $this->textField('Article 2 — catégorie', 'Finance', '#ressources .insight-card:nth-child(2) .meta span'),
                        $this->textField('Article 2 — date', '28 juillet 2025', '#ressources .insight-card:nth-child(2) .meta time'),
                        $this->textField('Article 2 — titre', 'Mise en conformité des Fonds non régulés : la fin de la tolérance administrative ?', '#ressources .insight-card:nth-child(2) h3', true),
                        $this->buttonField('Article 2 — lien', 'Lire l’analyse', '#ressources .insight-card:nth-child(2) .text-link'),
                        $this->imageField('Article 2 — image', '02-mise-en-conformite-featured.webp', '#ressources .insight-card:nth-child(2) img'),
                        $this->textField('Article 3 — catégorie', 'Formation', '#ressources .insight-card:nth-child(3) .meta span'),
                        $this->textField('Article 3 — date', '22 juillet 2025', '#ressources .insight-card:nth-child(3) .meta time'),
                        $this->textField('Article 3 — titre', 'Obligations de formation au Luxembourg : un enjeu stratégique de conformité', '#ressources .insight-card:nth-child(3) h3', true),
                        $this->buttonField('Article 3 — lien', 'Lire l’analyse', '#ressources .insight-card:nth-child(3) .text-link'),
                        $this->imageField('Article 3 — image', '03-obligations-formation-featured.webp', '#ressources .insight-card:nth-child(3) img'),
                        $this->buttonField('Bouton tous les articles', 'Voir toutes les analyses', '#ressources .insights-all .button'),
                    ]),
                    $this->section('cta', 'Appel à l’action final', '.home-page > .cta-section', [
                        $this->textField('Sur-titre', 'Prêt à démarrer ?', '.home-page > .cta-section .eyebrow'),
                        $this->textField('Titre', 'Besoin d’un consultant qualifié pour renforcer vos équipes ?', '.home-page > .cta-section h2'),
                        $this->textField('Description', 'Parlez-nous de votre besoin, on s’occupe du reste !', '.home-page > .cta-section > p', true),
                        $this->buttonField('Bouton principal', 'Commencer', '.home-page > .cta-section .button-primary'),
                        $this->buttonField('Bouton secondaire', 'Je suis consultant', '.home-page > .cta-section .button-quiet'),
                    ]),
                ],
            ],
            'services' => [
                'slug' => 'services', 'name' => 'Nos services', 'route' => 'app_expertises', 'path' => '/expertises', 'icon' => 'services',
                'description' => 'Expertises métier, solutions et plateforme e-learning.', 'sectionsCount' => 5, 'updated' => 'Hier, 16:08',
                'sections' => [
                    $this->section('hero', 'Introduction', '[data-section="hero"]', [
                        $this->textField('Chapitre', 'Consulting', '[data-section="hero"] .service-chapter-divider strong'),
                        $this->textField('Titre', 'Des consultants externes spécialisés.', '[data-section="hero"] h1'),
                        $this->textField('Introduction', 'Des expertises ciblées pour vos fonctions réglementées.', '[data-section="hero"] .lead', true),
                        $this->buttonField('Bouton principal', 'Déposer une mission', '[data-section="hero"] .button-primary'),
                        $this->textField('Badge 1', 'Finance & conformité', '[data-section="hero"] .intro-art span.pill:first-of-type'),
                        $this->textField('Badge 2', 'Des profils ciblés', '[data-section="hero"] .intro-art span.pill:nth-of-type(2)'),
                    ]),
                    $this->section('services', 'Domaines d’intervention', '[data-section="services"]', [
                        $this->textField('Sur-titre', 'Notre savoir-faire', '[data-section="services"] .eyebrow'),
                        $this->textField('Titre', 'Des expertises ciblées. Des réponses concrètes.', '[data-section="services"] h2'),
                        $this->buttonField('Filtre — Tous', 'Toutes les expertises', '.expertise-tabs .filter-button:nth-child(1)'),
                        $this->buttonField('Filtre — Conformité', 'Conformité', '.expertise-tabs .filter-button:nth-child(2)'),
                        $this->buttonField('Filtre — Finance', 'Finance', '.expertise-tabs .filter-button:nth-child(3)'),
                        $this->buttonField('Filtre — Transformation', 'Transformation', '.expertise-tabs .filter-button:nth-child(4)'),
                        $this->textField('Compliance — titre', 'Expertise Compliance', '#compliance h3'),
                        $this->textField('Compliance — description', 'Des consultants spécialisés pour accompagner les institutions financières dans leurs obligations AML et KYC.', '#compliance > p', true),
                        $this->textField('Compliance — tag 1', 'AML / CFT', '#compliance .tag-list .pill:nth-child(1)'),
                        $this->textField('Compliance — tag 2', 'KYC', '#compliance .tag-list .pill:nth-child(2)'),
                        $this->textField('Compliance — tag 3', 'Compliance Officer', '#compliance .tag-list .pill:nth-child(3)'),
                        $this->buttonField('Compliance — lien', 'Échanger sur ce besoin', '#compliance .text-link'),
                        $this->textField('Finance — titre', 'Services Fund Administration', '#finance h3'),
                        $this->textField('Finance — description', 'Des consultants spécialisés en comptabilité de fonds, NAV oversight et reporting.', '#finance > p', true),
                        $this->textField('Finance — tag 1', 'Fund Accounting', '#finance .tag-list .pill:nth-child(1)'),
                        $this->textField('Finance — tag 2', 'NAV Oversight', '#finance .tag-list .pill:nth-child(2)'),
                        $this->textField('Finance — tag 3', 'Reporting', '#finance .tag-list .pill:nth-child(3)'),
                        $this->buttonField('Finance — lien', 'Échanger sur ce besoin', '#finance .text-link'),
                        $this->textField('Risques — titre', 'Contrôle interne & gouvernance', '#risques h3'),
                        $this->textField('Risques — description', 'Nos consultants en audit et contrôle interne interviennent pour évaluer, améliorer et sécuriser les processus internes.', '#risques > p', true),
                        $this->textField('Risques — tag 1', 'Audit interne', '#risques .tag-list .pill:nth-child(1)'),
                        $this->textField('Risques — tag 2', 'Risk & Control', '#risques .tag-list .pill:nth-child(2)'),
                        $this->textField('Risques — tag 3', 'Gouvernance', '#risques .tag-list .pill:nth-child(3)'),
                        $this->buttonField('Risques — lien', 'Échanger sur ce besoin', '#risques .text-link'),
                        $this->textField('Juridique — titre', 'Services Juridiques & Réglementaires', '#juridique h3'),
                        $this->textField('Juridique — description', 'Nos juristes et consultants vous assistent dans la rédaction contractuelle, l’analyse réglementaire et le risque juridique.', '#juridique > p', true),
                        $this->textField('Juridique — tag 1', 'Legal Officer', '#juridique .tag-list .pill:nth-child(1)'),
                        $this->textField('Juridique — tag 2', 'Fonds', '#juridique .tag-list .pill:nth-child(2)'),
                        $this->textField('Juridique — tag 3', 'Banque privée', '#juridique .tag-list .pill:nth-child(3)'),
                        $this->buttonField('Juridique — lien', 'Échanger sur ce besoin', '#juridique .text-link'),
                        $this->textField('Digital — titre', 'Services Transformation Digitale (IT)', '#digital h3'),
                        $this->textField('Digital — description', 'Des experts IT pour renforcer vos équipes et piloter vos projets de gouvernance, opérations et cybersécurité.', '#digital > p', true),
                        $this->textField('Digital — tag 1', 'Gestion de projet', '#digital .tag-list .pill:nth-child(1)'),
                        $this->textField('Digital — tag 2', 'Cybersécurité', '#digital .tag-list .pill:nth-child(2)'),
                        $this->textField('Digital — tag 3', 'Gouvernance IT', '#digital .tag-list .pill:nth-child(3)'),
                        $this->buttonField('Digital — lien', 'Échanger sur ce besoin', '#digital .text-link'),
                        $this->textField('Carte sur mesure — titre', 'Votre besoin ne rentre pas dans une case ?', '[data-section="services"] .service-card.custom h3'),
                        $this->textField('Carte sur mesure — description', 'Nous construisons des solutions sur mesure pour vos besoins non standards ou multisectoriels.', '[data-section="services"] .service-card.custom p', true),
                        $this->buttonField('Carte sur mesure — bouton', 'Déposer une mission', '[data-section="services"] .service-card.custom .button'),
                    ]),
                    $this->section('training-divider', 'Séparateur formation', '[data-section="training-divider"]', [
                        $this->textField('Chapitre', 'Training', '[data-section="training-divider"] .service-chapter-divider strong'),
                    ]),
                    $this->section('plateforme', 'Plateforme e-learning', '[data-section="plateforme"]', [
                        $this->textField('Sur-titre', 'Notre solution digitale', '[data-section="plateforme"] .eyebrow'),
                        $this->textField('Titre', 'La formation réglementaire. Accessible partout.', '[data-section="plateforme"] h2'),
                        $this->textField('Description', 'Une plateforme e-learning pensée pour les professionnels et les équipes réglementées au Luxembourg.', '[data-section="plateforme"] .platform-lead', true),
                        $this->textField('Avantage 1 — titre', '30+ modules métier', '[data-section="plateforme"] .platform-benefit:first-child strong'),
                        $this->textField('Avantage 1 — description', 'Compliance, AML/CFT, Risk, GDPR et Cybersécurité.', '[data-section="plateforme"] .platform-benefit:first-child small'),
                        $this->textField('Avantage 2 — titre', 'Suivi centralisé', '[data-section="plateforme"] .platform-benefit:nth-child(2) strong'),
                        $this->textField('Avantage 2 — description', 'Progression, complétion et certificats visibles en temps réel.', '[data-section="plateforme"] .platform-benefit:nth-child(2) small'),
                        $this->textField('Avantage 3 — titre', 'Web & mobile', '[data-section="plateforme"] .platform-benefit:nth-child(3) strong'),
                        $this->textField('Avantage 3 — description', 'Vos parcours restent disponibles 24/7, au bureau comme en mobilité.', '[data-section="plateforme"] .platform-benefit:nth-child(3) small'),
                        $this->buttonField('Bouton plateforme', 'Découvrir la plateforme', '[data-section="plateforme"] .platform-main-link'),
                        $this->textField('Téléchargements — introduction', 'Emportez vos formations partout', '[data-section="plateforme"] .app-downloads-label'),
                        $this->textField('App Store — sur-titre', 'Télécharger sur', '[data-section="plateforme"] .store-badge:first-child small'),
                        $this->textField('App Store — titre', 'App Store', '[data-section="plateforme"] .store-badge:first-child strong'),
                        $this->textField('Google Play — sur-titre', 'Disponible sur', '[data-section="plateforme"] .store-badge:nth-child(2) small'),
                        $this->textField('Google Play — titre', 'Google Play', '[data-section="plateforme"] .store-badge:nth-child(2) strong'),
                        $this->textField('Aperçu — catalogue', 'Catalogue', '[data-section="plateforme"] .platform-floating-modules > span'),
                        $this->textField('Aperçu — modules', '30+ modules', '[data-section="plateforme"] .platform-floating-modules strong'),
                        $this->textField('Aperçu — actualisation', 'Actualisés régulièrement', '[data-section="plateforme"] .platform-floating-modules small'),
                        $this->textField('Aperçu — progression', 'Progression des équipes', '[data-section="plateforme"] .platform-floating-progress > span'),
                        $this->textField('Aperçu — suivi', 'Suivi en temps réel', '[data-section="plateforme"] .platform-floating-progress strong'),
                        $this->textField('Aperçu — appareils', 'Disponible sur tous vos appareils', '[data-section="plateforme"] .platform-device-chip'),
                        $this->imageField('Capture de la plateforme', 'plateforme.webp', '[data-section="plateforme"] .platform-screen img'),
                    ]),
                    $this->section('cta', 'Appel à l’action final', '.cta-section', [
                        $this->textField('Sur-titre', 'Prêt à démarrer ?', '.cta-section .eyebrow'),
                        $this->textField('Titre', 'Besoin d’un consultant qualifié pour renforcer vos équipes ?', '.cta-section h2'),
                        $this->textField('Description', 'Parlez-nous de votre besoin, on s’occupe du reste !', '.cta-section > p', true),
                        $this->buttonField('Bouton principal', 'Commencer', '.cta-section .button-primary'),
                        $this->buttonField('Bouton secondaire', 'Je suis consultant', '.cta-section .button-quiet'),
                    ]),
                ],
            ],
            'about' => [
                'slug' => 'about', 'name' => 'À propos', 'route' => 'app_cabinet', 'path' => '/a-propos', 'icon' => 'people',
                'description' => 'Présentation, mission, valeurs et réseau de consultants.', 'sectionsCount' => 5, 'updated' => '14 sept. 2026',
                'sections' => [
                    $this->section('hero', 'Présentation du cabinet', '[data-section="hero"]', [
                        $this->textField('Sur-titre', 'À propos', '[data-section="hero"] > .eyebrow'),
                        $this->textField('Titre', 'Un partenaire de confiance pour vos missions critiques.', '[data-section="hero"] h1'),
                        $this->textField('Introduction', 'Un cabinet indépendant spécialisé dans les métiers régulés au Luxembourg.', '[data-section="hero"] .lead', true),
                        $this->textField('Légende — signature', 'LES CONSULTANTS · CONSEIL & FORMATION', '[data-section="hero"] .photo-caption small'),
                        $this->textField('Légende — expertise', 'Expertise finance. Et conformité.', '[data-section="hero"] .photo-caption p'),
                        $this->imageField('Photo principale', 'cabinet-luxembourg.webp', '[data-section="hero"] img'),
                    ]),
                    $this->section('mission', 'Notre mission', '[data-section="mission"]', [
                        $this->textField('Sur-titre', 'À propos', '[data-section="mission"] .eyebrow'),
                        $this->textField('Titre', 'Un partenaire de confiance pour vos missions critiques.', '[data-section="mission"] h2'),
                        $this->textField('Texte principal', 'Nous connectons les entreprises du secteur financier au Luxembourg avec nos consultants spécialisés.', '[data-section="mission"] > div:nth-child(2) p:first-child', true),
                        $this->textField('Texte secondaire', 'Notre approche repose sur l’écoute, la réactivité et la compréhension des enjeux.', '[data-section="mission"] > div:nth-child(2) p:nth-child(2)', true),
                        $this->buttonField('Lien', 'Parlons de votre projet', '[data-section="mission"] .text-link'),
                    ]),
                    $this->section('values', 'Nos différences', '[data-section="values"]', [
                        $this->textField('Sur-titre', 'Notre mission', '[data-section="values"] .eyebrow'),
                        $this->textField('Titre', 'Ce qui fait Notre Différence.', '[data-section="values"] h2'),
                        $this->textField('Valeur 1 — repère', '01 / RÉACTIVITÉ', '.values-grid > div:first-child .value-number'),
                        $this->textField('Valeur 1 — titre', 'Réactivité & Sourcing rapide', '.values-grid > div:first-child h3'),
                        $this->textField('Valeur 1 — description', 'Des profils disponibles sous 48 heures, soigneusement sélectionnés et validés.', '.values-grid > div:first-child p', true),
                        $this->textField('Valeur 2 — repère', '02 / EXPERTISE', '.values-grid > div:nth-child(2) .value-number'),
                        $this->textField('Valeur 2 — titre', 'Compréhension des enjeux métier', '.values-grid > div:nth-child(2) h3'),
                        $this->textField('Valeur 2 — description', 'Une spécialisation en conformité et métiers de la finance pour proposer des consultants immédiatement opérationnels.', '.values-grid > div:nth-child(2) p', true),
                        $this->textField('Valeur 3 — repère', '03 / SUIVI', '.values-grid > div:nth-child(3) .value-number'),
                        $this->textField('Valeur 3 — titre', 'Suivi & Accompagnement', '.values-grid > div:nth-child(3) h3'),
                        $this->textField('Valeur 3 — description', 'Un accompagnement personnalisé et un processus simple, rapide et transparent pendant toute la mission.', '.values-grid > div:nth-child(3) p', true),
                    ]),
                    $this->section('reseau', 'Rejoindre le réseau', '[data-section="reseau"]', [
                        $this->textField('Légende de l’image', 'Luxembourg · Belgique · Europe', '[data-section="reseau"] .image-stamp'),
                        $this->textField('Sur-titre', 'Vous êtes consultant ?', '[data-section="reseau"] .eyebrow'),
                        $this->textField('Titre', 'Votre expertise mérite le bon projet.', '[data-section="reseau"] h2'),
                        $this->textField('Description', 'Rejoignez notre réseau pour recevoir des missions ciblées, alignées avec votre expertise.', '[data-section="reseau"] h2 + p', true),
                        $this->buttonField('Bouton', 'Nous contacter', '[data-section="reseau"] .button'),
                        $this->imageField('Photo du réseau', 'reseau-consultants.webp', '[data-section="reseau"] img'),
                    ]),
                    $this->section('cta', 'Appel à l’action final', '.cta-section', [
                        $this->textField('Sur-titre', 'Prêt à passer à l’action ?', '.cta-section .eyebrow'),
                        $this->textField('Titre', 'Parlez-nous de votre besoin, on s’occupe du reste !', '.cta-section h2'),
                        $this->textField('Description', 'Des profils ciblés et immédiatement opérationnels pour vos missions au Luxembourg.', '.cta-section > p', true),
                        $this->buttonField('Bouton principal', 'Déposer une mission', '.cta-section .button-primary'),
                        $this->buttonField('Bouton secondaire', 'Je suis consultant', '.cta-section .button-quiet'),
                    ]),
                ],
            ],
            'contact' => [
                'slug' => 'contact', 'name' => 'Contact', 'route' => 'app_contact', 'path' => '/contact', 'icon' => 'mail',
                'description' => 'Coordonnées, formulaire de contact et localisation.', 'sectionsCount' => 3, 'updated' => '10 sept. 2026',
                'sections' => [
                    $this->section('contact', 'Introduction & coordonnées', '.contact-layout', [
                        $this->textField('Sur-titre', 'Contact', '.contact-copy .eyebrow'),
                        $this->textField('Titre', 'Un besoin, une question ? Contactez-nous.', '.contact-copy h1'),
                        $this->textField('Introduction', 'Notre équipe est à votre disposition pour toute demande d’information.', '.contact-copy .lead', true),
                        $this->textField('Message de confiance', 'Un échange direct avec notre équipe', '.contact-copy > .pill'),
                        $this->textField('Libellé e-mail', 'Email', '.contact-detail:first-child small'),
                        $this->textField('E-mail', 'pruffin@les-consultants.lu', '.contact-detail:first-child a'),
                        $this->textField('Libellé téléphone', 'Téléphone', '.contact-detail:nth-child(2) small'),
                        $this->textField('Téléphone principal', '+352 621 735 201', '.contact-detail:nth-child(2) a:first-of-type'),
                        $this->textField('Téléphone secondaire', '+33 7 66 50 14 44', '.contact-detail:nth-child(2) a:nth-of-type(2)'),
                        $this->textField('Libellé adresse', 'Adresse', '.contact-detail:nth-child(3) small'),
                        $this->textField('Adresse', '1 Simmerfarm, L-8363 Simmerfarm, Luxembourg', '.contact-detail:nth-child(3) p', true),
                    ]),
                    $this->section('form', 'Formulaire', '.contact-form-wrap', [
                        $this->textField('Sur-titre', 'Envoyez-nous un message', '.contact-form-wrap .eyebrow'),
                        $this->textField('Titre', 'Nous vous répondons sous 24h.', '.contact-form-wrap h2'),
                        $this->buttonField('Onglet entreprise', 'Je suis une entreprise', '.format-switch button:first-child'),
                        $this->buttonField('Onglet consultant', 'Je suis consultant', '.format-switch button:nth-child(2)'),
                        $this->textField('Champ nom — libellé', 'Nom *', 'label[for="contact_request_name"]'),
                        $this->attributeField('Champ nom — exemple', 'Votre nom', '#contact_request_name', 'placeholder'),
                        $this->textField('Champ e-mail — libellé', 'Email *', 'label[for="contact_request_email"]'),
                        $this->attributeField('Champ e-mail — exemple', 'vous@entreprise.com', '#contact_request_email', 'placeholder'),
                        $this->textField('Champ téléphone — libellé', 'Téléphone', 'label[for="contact_request_phone"]'),
                        $this->attributeField('Champ téléphone — exemple', '+352 …', '#contact_request_phone', 'placeholder'),
                        $this->attributeField('Sujet entreprise — libellé', 'Sujet *', '.contact-form-wrap[data-contact-form-enterprise-subject-label-value]', 'data-contact-form-enterprise-subject-label-value'),
                        $this->attributeField('Sujet consultant — libellé', 'Votre domaine d’expertise *', '.contact-form-wrap[data-contact-form-consultant-subject-label-value]', 'data-contact-form-consultant-subject-label-value'),
                        $this->textField('Sujet — choix par défaut', 'Choisir un sujet', '#contact_request_subject option[value=""]'),
                        $this->textField('Sujet — Compliance', 'Compliance & réglementation', '#contact_request_subject option[value="compliance"]'),
                        $this->textField('Sujet — Finance', 'Finance & fonds', '#contact_request_subject option[value="finance"]'),
                        $this->textField('Sujet — Risques', 'Risques & gouvernance', '#contact_request_subject option[value="risques"]'),
                        $this->textField('Sujet — Juridique', 'Juridique & réglementaire', '#contact_request_subject option[value="juridique"]'),
                        $this->textField('Sujet — Transformation', 'Transformation & IT', '#contact_request_subject option[value="digital"]'),
                        $this->textField('Sujet — Formation', 'Formation', '#contact_request_subject option[value="formation"]'),
                        $this->textField('Sujet — Autre', 'Autre besoin', '#contact_request_subject option[value="autre"]'),
                        $this->attributeField('Message entreprise — libellé', 'Votre message *', '.contact-form-wrap[data-contact-form-enterprise-message-label-value]', 'data-contact-form-enterprise-message-label-value'),
                        $this->attributeField('Message consultant — libellé', 'Votre parcours et vos disponibilités *', '.contact-form-wrap[data-contact-form-consultant-message-label-value]', 'data-contact-form-consultant-message-label-value'),
                        $this->attributeField('Champ message — exemple', 'Votre besoin, le contexte et votre calendrier…', '#contact_request_message', 'placeholder'),
                        $this->textField('Consentement', 'J’accepte que mes informations soient utilisées pour répondre à ma demande. *', 'label[for="contact_request_consent"]', true),
                        $this->buttonField('Bouton d’envoi', 'Envoyer', '.contact-form-wrap button[type="submit"]'),
                    ]),
                    $this->section('map', 'Adresse & carte', '.contact-map-section', [
                        $this->textField('Sur-titre', 'Notre adresse', '.contact-map-copy .eyebrow'),
                        $this->textField('Titre', 'Retrouvez-nous au Luxembourg.', '.contact-map-section h2'),
                        $this->textField('Description', 'Au cœur de notre écosystème, à proximité directe de nos clients et de nos consultants.', '.contact-map-copy > p', true),
                        $this->textField('Adresse', '1 Simmerfarm, L-8363 Simmerfarm, Luxembourg', '.contact-map-copy address', true),
                        $this->buttonField('Lien Google Maps', 'Ouvrir dans Google Maps', '.contact-map-copy .text-link'),
                        $this->textField('Carte — nom', 'Les Consultants', '.map-home-label strong'),
                        $this->textField('Carte — adresse courte', '1 Simmerfarm', '.map-home-label small'),
                    ]),
                ],
            ],
        ];

        return $pages[$slug];
    }

    /** @return array<string, mixed> */
    private function componentDefinition(string $slug): array
    {
        $components = [
            'header' => [
                'slug' => 'header', 'name' => 'Menu & navigation', 'route' => 'app_home', 'path' => '/', 'scope' => 'Toutes les pages', 'icon' => 'menu', 'kind' => 'component',
                'description' => 'Logo, navigation principale, appel à l’action et menu mobile.', 'sectionsCount' => 2, 'updated' => 'Contenu actuel',
                'sections' => [
                    $this->section('main_navigation', 'Navigation principale', '.site-header', [
                        $this->imageField('Logo', 'logo-original.png', '.site-header .brand-symbol img'),
                        $this->textField('Nom de la marque', 'les consultants', '.site-header .brand-title'),
                        $this->textField('Signature de marque', 'CONSEIL & FORMATION', '.site-header .brand-name small'),
                        $this->buttonField('Lien Accueil', 'Accueil', '.site-header .main-nav > a:nth-child(1)'),
                        $this->buttonField('Lien Services', 'Nos services', '.site-header .main-nav > a:nth-child(2)'),
                        $this->buttonField('Lien À propos', 'À propos', '.site-header .main-nav > a:nth-child(3)'),
                        $this->buttonField('Lien Contact', 'Contact', '.site-header .main-nav > a:nth-child(4)'),
                        $this->buttonField('Mission — menu déroulant', 'Missions', '.site-header .main-nav > .mobile-cta'),
                        $this->buttonField('Lien E-learning', 'E-learning', '.site-header .header-actions > .text-link'),
                        $this->buttonField('Bouton mission — ordinateur', 'Déposer une mission', '.site-header .button-primary .desktop-label'),
                        $this->buttonField('Bouton mission — mobile', 'Missions', '.site-header .button-primary .mobile-label'),
                    ]),
                    $this->section('mobile_navigation', 'Barre de navigation mobile', '.mobile-bottom-nav', [
                        $this->buttonField('Lien Accueil', 'Accueil', '.mobile-bottom-nav .mobile-nav-item:nth-child(1) > span:last-child'),
                        $this->buttonField('Lien Services', 'Services', '.mobile-bottom-nav .mobile-nav-item:nth-child(2) > span:last-child'),
                        $this->buttonField('Action centrale Mission', 'Mission', '.mobile-bottom-nav .mobile-nav-item:nth-child(3) > span:last-child'),
                        $this->buttonField('Lien À propos', 'À propos', '.mobile-bottom-nav .mobile-nav-item:nth-child(4) > span:last-child'),
                        $this->buttonField('Lien Contact', 'Contact', '.mobile-bottom-nav .mobile-nav-item:nth-child(5) > span:last-child'),
                    ]),
                ],
            ],
            'footer' => [
                'slug' => 'footer', 'name' => 'Pied de page', 'route' => 'app_home', 'path' => '/', 'scope' => 'Toutes les pages', 'icon' => 'services', 'kind' => 'component',
                'description' => 'Identité, liens utiles, coordonnées et mentions de bas de page.', 'sectionsCount' => 5, 'updated' => 'Contenu actuel',
                'sections' => [
                    $this->section('footer_brand', 'Identité', '.site-footer .footer-brand', [
                        $this->imageField('Logo', 'logo-original.png', '.site-footer .brand-symbol img'),
                        $this->textField('Nom de la marque', 'les consultants', '.site-footer .brand-title'),
                        $this->textField('Signature de marque', 'CONSEIL & FORMATION', '.site-footer .brand-name small'),
                        $this->textField('Présentation courte', 'Conseil & FORMATION au Luxembourg', '.site-footer .footer-brand > p'),
                    ]),
                    $this->section('footer_services', 'Colonne Services', '.site-footer .footer-top > .footer-column:nth-child(2)', [
                        $this->textField('Titre', 'Nos services', '.site-footer .footer-top > .footer-column:nth-child(2) h3'),
                        $this->buttonField('Lien Conseil', 'Conseil', '.site-footer .footer-top > .footer-column:nth-child(2) a:nth-of-type(1)'),
                        $this->buttonField('Lien E-learning', 'E-Learning ↗', '.site-footer .footer-top > .footer-column:nth-child(2) a:nth-of-type(2)'),
                    ]),
                    $this->section('footer_links', 'Colonne Liens utiles', '.site-footer .footer-top > .footer-column:nth-child(3)', [
                        $this->textField('Titre', 'Liens utiles', '.site-footer .footer-top > .footer-column:nth-child(3) h3'),
                        $this->buttonField('Lien Accueil', 'Accueil', '.site-footer .footer-top > .footer-column:nth-child(3) a:nth-of-type(1)'),
                        $this->buttonField('Lien À propos', 'Qui sommes-nous', '.site-footer .footer-top > .footer-column:nth-child(3) a:nth-of-type(2)'),
                        $this->buttonField('Lien Expertises', 'Nos expertises', '.site-footer .footer-top > .footer-column:nth-child(3) a:nth-of-type(3)'),
                        $this->buttonField('Lien Blog', 'Blog', '.site-footer .footer-top > .footer-column:nth-child(3) a:nth-of-type(4)'),
                        $this->buttonField('Lien Mission', 'Déposer une mission', '.site-footer .footer-top > .footer-column:nth-child(3) a:nth-of-type(5)'),
                    ]),
                    $this->section('footer_contact', 'Colonne Contact', '.site-footer .footer-top > .footer-column:nth-child(4)', [
                        $this->textField('Titre', 'Contact', '.site-footer .footer-top > .footer-column:nth-child(4) h3'),
                        $this->textField('Téléphone Luxembourg', '+352 621 735 201', '.site-footer .footer-top > .footer-column:nth-child(4) a:nth-of-type(1)'),
                        $this->textField('Téléphone France', '+33 7 66 50 14 44', '.site-footer .footer-top > .footer-column:nth-child(4) a:nth-of-type(2)'),
                        $this->textField('Adresse e-mail', 'pruffin@les-consultants.lu', '.site-footer .footer-top > .footer-column:nth-child(4) a:nth-of-type(3)'),
                        $this->textField('Adresse — première ligne', '1 Simmerfarm, L-8363', '.site-footer .footer-address-line:first-child'),
                        $this->textField('Adresse — seconde ligne', 'Luxembourg', '.site-footer .footer-address-line:last-child'),
                    ]),
                    $this->section('footer_legal', 'Mentions de bas de page', '.site-footer .footer-bottom', [
                        $this->textField('Copyright', 'LES CONSULTANTS — Tous droits réservés', '.site-footer .footer-copyright-label'),
                        $this->buttonField('Lien Mentions légales', 'Mentions légales', '.site-footer .footer-bottom > div a:nth-child(1)'),
                        $this->buttonField('Lien Confidentialité', 'Politique de confidentialité', '.site-footer .footer-bottom > div a:nth-child(2)'),
                        $this->buttonField('Lien Contact', 'Contact', '.site-footer .footer-bottom > div a:nth-child(3)'),
                    ]),
                ],
            ],
        ];

        return $components[$slug];
    }

    /** @param list<array<string, mixed>> $fields
     *  @return array<string, mixed>
     */
    private function section(string $id, string $label, string $selector, array $fields, ?array $carousel = null): array
    {
        $fieldsCount = count($fields);

        if (null !== $carousel) {
            foreach ($carousel['cards'] as $card) {
                $fieldsCount += count($card['fields']);
            }
        }

        return compact('id', 'label', 'selector', 'fields', 'fieldsCount', 'carousel');
    }

    /** @param list<array<string, mixed>> $cards
     *  @return array<string, mixed>
     */
    private function carousel(int $interval, string $viewportSelector, string $trackSelector, array $cards): array
    {
        return ['interval' => $interval, 'mode' => 'cards', 'viewportSelector' => $viewportSelector, 'trackSelector' => $trackSelector, 'cards' => $cards];
    }

    /** @param list<array<string, mixed>> $fields
     *  @return array<string, mixed>
     */
    private function carouselCard(string $label, string $selector, array $fields): array
    {
        return compact('label', 'selector', 'fields');
    }

    /** @return array<string, mixed> */
    private function textField(string $label, string $value, string $selector, bool $multiline = false): array
    {
        return ['type' => $multiline ? 'textarea' : 'text', 'label' => $label, 'value' => $value, 'selector' => $selector];
    }

    /** @return array<string, mixed> */
    private function buttonField(string $label, string $value, string $selector): array
    {
        return ['type' => 'button', 'label' => $label, 'value' => $value, 'selector' => $selector];
    }

    /** @return array<string, mixed> */
    private function imageField(string $label, string $value, string $selector): array
    {
        return ['type' => 'image', 'label' => $label, 'value' => $value, 'selector' => $selector];
    }

    /** @return array<string, mixed> */
    private function attributeField(string $label, string $value, string $selector, string $attribute): array
    {
        return ['type' => 'attribute', 'label' => $label, 'value' => $value, 'selector' => $selector, 'attribute' => $attribute];
    }
}
