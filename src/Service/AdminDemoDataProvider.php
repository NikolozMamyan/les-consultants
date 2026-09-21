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

    /** @return array<string, mixed>|null */
    public function page(string $slug): ?array
    {
        if (!in_array($slug, ['home', 'services', 'about', 'contact'], true)) {
            return null;
        }

        return $this->pageDefinition($slug);
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
                        $this->buttonField('Bouton principal', 'Déposer une mission', '.hero .button-primary'),
                        $this->buttonField('Bouton secondaire', 'Découvrir nos expertises', '.hero .button-quiet'),
                    ]),
                    $this->section('radar', 'Radar des experts', '.radar-section', [
                        $this->textField('Sur-titre', 'Notre réseau d’experts', '.radar-section .eyebrow'),
                        $this->textField('Titre', 'Les bons experts. Au bon moment.', '.radar-section h2'),
                        $this->textField('Description', 'Nous qualifions votre besoin, activons notre réseau et vous présentons des consultants spécialisés, disponibles et adaptés à votre environnement.', '.radar-section-intro p', true),
                        $this->buttonField('Bouton', 'Confier une mission', '.radar-section-intro .button'),
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
                            $this->textField('Titre', 'Compliance & réglementation', '[data-section="expertises"] .expertise-card:nth-child(1) h3'),
                            $this->textField('Description', 'Nous vous accompagnons dans la mise en conformité, le renforcement des dispositifs de contrôle et l’anticipation des évolutions réglementaires.', '[data-section="expertises"] .expertise-card:nth-child(1) > p', true),
                            $this->buttonField('Lien', 'Renforcer ma conformité', '[data-section="expertises"] .expertise-card:nth-child(1) .text-link'),
                        ]),
                        $this->carouselCard('Finance & fonds', '[data-section="expertises"] .expertise-card:nth-child(2)', [
                            $this->textField('Titre', 'Finance & fonds d’investissement', '[data-section="expertises"] .expertise-card:nth-child(2) h3'),
                            $this->textField('Description', 'Nous accompagnons les sociétés de gestion et administrateurs de fonds dans leurs fonctions opérationnelles, financières et réglementaires.', '[data-section="expertises"] .expertise-card:nth-child(2) > p', true),
                            $this->buttonField('Lien', 'Accompagner mes opérations', '[data-section="expertises"] .expertise-card:nth-child(2) .text-link'),
                        ]),
                        $this->carouselCard('Risques & gouvernance', '[data-section="expertises"] .expertise-card:nth-child(3)', [
                            $this->textField('Titre', 'Risques & gouvernance', '[data-section="expertises"] .expertise-card:nth-child(3) h3'),
                            $this->textField('Description', 'Nous renforçons vos dispositifs de contrôle interne, de gestion des risques et de gouvernance avec des experts immédiatement opérationnels.', '[data-section="expertises"] .expertise-card:nth-child(3) > p', true),
                            $this->buttonField('Lien', 'Structurer mes dispositifs', '[data-section="expertises"] .expertise-card:nth-child(3) .text-link'),
                        ]),
                        $this->carouselCard('Transformation & IT', '[data-section="expertises"] .expertise-card:nth-child(4)', [
                            $this->textField('Titre', 'Transformation & IT', '[data-section="expertises"] .expertise-card:nth-child(4) h3'),
                            $this->textField('Description', 'Nous vous aidons à structurer, piloter et sécuriser vos projets de transformation digitale dans un environnement financier exigeant.', '[data-section="expertises"] .expertise-card:nth-child(4) > p', true),
                            $this->buttonField('Lien', 'Faire avancer mes projets', '[data-section="expertises"] .expertise-card:nth-child(4) .text-link'),
                        ]),
                    ])),
                    $this->section('approche', 'Notre approche', '[data-section="approche"]', [
                        $this->textField('Sur-titre', 'Notre approche', '[data-section="approche"] .eyebrow'),
                        $this->textField('Titre', 'Un accompagnement simple et efficace.', '[data-section="approche"] h2'),
                        $this->textField('Description', 'De la compréhension du besoin au suivi de la mission, chaque étape est structurée pour vous faire gagner du temps.', '[data-section="approche"] .approach-heading > p', true),
                        $this->textField('Étape 1 — titre', 'Mise en relations', '[data-section="approche"] .approach-tab:first-child h3'),
                        $this->textField('Étape 1 — description', 'Nous identifions des consultants hautement qualifiés et disponibles.', '[data-section="approche"] .approach-tab:first-child p', true),
                        $this->textField('Étape 2 — titre', 'Expertise secteur', '[data-section="approche"] .approach-tab:nth-child(2) h3'),
                        $this->textField('Étape 3 — titre', 'Processus fluide', '[data-section="approche"] .approach-tab:nth-child(3) h3'),
                    ]),
                    $this->section('formation', 'Formation professionnelle', '[data-section="formation"]', [
                        $this->textField('Sur-titre', 'Formation professionnelle', '[data-section="formation"] .eyebrow'),
                        $this->textField('Titre', 'Formez vos équipes. Suivez les compétences. Gagnez en conformité.', '[data-section="formation"] h2'),
                        $this->textField('Description', 'Des formations conçues pour les professionnels du secteur financier au Luxembourg.', '[data-section="formation"] > div:first-child > p', true),
                        $this->buttonField('Bouton principal', 'Découvrir l’e-learning', '[data-section="formation"] .button'),
                        $this->buttonField('Lien secondaire', 'Nous contacter', '[data-section="formation"] .text-link'),
                    ]),
                    $this->section('cabinet', 'Présentation du cabinet', '[data-section="cabinet"]', [
                        $this->textField('Sur-titre', 'À propos', '[data-section="cabinet"] .eyebrow'),
                        $this->textField('Titre', 'Experts en finance & conformité', '[data-section="cabinet"] h2'),
                        $this->textField('Description', 'Notre mission est simple : connecter les meilleurs consultants avec les acteurs clés du secteur financier.', '[data-section="cabinet"] h2 + p', true),
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
                    ]),
                    $this->section('resources', 'Actualités & conseils', '#ressources', [
                        $this->textField('Sur-titre', 'Regards sur nos métiers', '#ressources .eyebrow'),
                        $this->textField('Titre', 'Actualités & conseils du secteur', '#ressources h2'),
                        $this->textField('Description', 'Retrouvez les éclairages publiés par le cabinet.', '#ressources .section-heading > p', true),
                        $this->textField('Article 1 — titre', 'Freelances & Entreprises : une nouvelle manière de collaborer', '#ressources .insight-card:first-child h3'),
                        $this->imageField('Article 1 — image', 'conseil.jpg', '#ressources .insight-card:first-child img'),
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
                'description' => 'Expertises métier, solutions et plateforme e-learning.', 'sectionsCount' => 4, 'updated' => 'Hier, 16:08',
                'sections' => [
                    $this->section('hero', 'Introduction', '[data-section="hero"]', [
                        $this->textField('Titre', 'Des consultants externes spécialisés.', '[data-section="hero"] h1'),
                        $this->textField('Introduction', 'Des expertises ciblées pour vos fonctions réglementées.', '[data-section="hero"] .lead', true),
                        $this->buttonField('Bouton principal', 'Déposer une mission', '[data-section="hero"] .button-primary'),
                    ]),
                    $this->section('services', 'Domaines d’intervention', '[data-section="services"]', [
                        $this->textField('Sur-titre', 'Nos expertises', '[data-section="services"] .eyebrow'),
                        $this->textField('Titre', 'Des expertises ciblées. Des réponses concrètes.', '[data-section="services"] h2'),
                        $this->textField('Compliance — titre', 'Expertise Compliance', '#compliance h3'),
                        $this->textField('Compliance — description', 'Des consultants spécialisés pour accompagner les institutions financières dans leurs obligations AML et KYC.', '#compliance > p', true),
                        $this->buttonField('Compliance — lien', 'Échanger sur ce besoin', '#compliance .text-link'),
                        $this->textField('Finance — titre', 'Services Fund Administration', '#finance h3'),
                        $this->textField('Finance — description', 'Des consultants spécialisés en comptabilité de fonds, NAV oversight et reporting.', '#finance > p', true),
                        $this->buttonField('Carte sur mesure — bouton', 'Déposer une mission', '[data-section="services"] .service-card.custom .button'),
                    ]),
                    $this->section('plateforme', 'Plateforme e-learning', '[data-section="plateforme"]', [
                        $this->textField('Sur-titre', 'Notre solution digitale', '[data-section="plateforme"] .eyebrow'),
                        $this->textField('Titre', 'La formation réglementaire. Accessible partout.', '[data-section="plateforme"] h2'),
                        $this->textField('Description', 'Une plateforme e-learning pensée pour les professionnels et les équipes réglementées au Luxembourg.', '[data-section="plateforme"] .platform-lead', true),
                        $this->textField('Avantage 1 — titre', '30+ modules métier', '[data-section="plateforme"] .platform-benefit:first-child strong'),
                        $this->textField('Avantage 2 — titre', 'Suivi centralisé', '[data-section="plateforme"] .platform-benefit:nth-child(2) strong'),
                        $this->textField('Avantage 3 — titre', 'Web & mobile', '[data-section="plateforme"] .platform-benefit:nth-child(3) strong'),
                        $this->buttonField('Bouton plateforme', 'Découvrir la plateforme', '[data-section="plateforme"] .platform-main-link'),
                        $this->imageField('Capture de la plateforme', 'plateforme.webp', '[data-section="plateforme"] .platform-screen img'),
                    ]),
                    $this->section('cta', 'Appel à l’action final', '.cta-section', [
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
                        $this->textField('Titre', 'Un partenaire de confiance pour vos missions critiques.', '[data-section="hero"] h1'),
                        $this->textField('Introduction', 'Un cabinet indépendant spécialisé dans les métiers régulés au Luxembourg.', '[data-section="hero"] .lead', true),
                        $this->imageField('Photo principale', 'cabinet-luxembourg.webp', '[data-section="hero"] img'),
                    ]),
                    $this->section('mission', 'Notre mission', '[data-section="mission"]', [
                        $this->textField('Titre', 'Un partenaire de confiance pour vos missions critiques.', '[data-section="mission"] h2'),
                        $this->textField('Texte principal', 'Nous connectons les entreprises du secteur financier au Luxembourg avec nos consultants spécialisés.', '[data-section="mission"] > div:nth-child(2) p:first-child', true),
                        $this->textField('Texte secondaire', 'Notre approche repose sur l’écoute, la réactivité et la compréhension des enjeux.', '[data-section="mission"] > div:nth-child(2) p:nth-child(2)', true),
                        $this->buttonField('Lien', 'Parlons de votre projet', '[data-section="mission"] .text-link'),
                    ]),
                    $this->section('values', 'Nos différences', '.values-grid', [
                        $this->textField('Valeur 1 — titre', 'Réactivité & Sourcing rapide', '.values-grid > div:first-child h3'),
                        $this->textField('Valeur 1 — description', 'Des profils disponibles sous 48 heures, soigneusement sélectionnés et validés.', '.values-grid > div:first-child p', true),
                        $this->textField('Valeur 2 — titre', 'Compréhension des enjeux métier', '.values-grid > div:nth-child(2) h3'),
                        $this->textField('Valeur 3 — titre', 'Suivi & Accompagnement', '.values-grid > div:nth-child(3) h3'),
                    ]),
                    $this->section('reseau', 'Rejoindre le réseau', '[data-section="reseau"]', [
                        $this->textField('Titre', 'Votre expertise mérite le bon projet.', '[data-section="reseau"] h2'),
                        $this->textField('Description', 'Rejoignez notre réseau pour recevoir des missions ciblées, alignées avec votre expertise.', '[data-section="reseau"] h2 + p', true),
                        $this->buttonField('Bouton', 'Nous contacter', '[data-section="reseau"] .button'),
                        $this->imageField('Photo du réseau', 'reseau-consultants.webp', '[data-section="reseau"] img'),
                    ]),
                    $this->section('cta', 'Appel à l’action final', '.cta-section', [
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
                        $this->textField('Titre', 'Un besoin, une question ? Contactez-nous.', '.contact-copy h1'),
                        $this->textField('Introduction', 'Notre équipe est à votre disposition pour toute demande d’information.', '.contact-copy .lead', true),
                        $this->textField('E-mail', 'pruffin@les-consultants.lu', '.contact-detail:first-child a'),
                        $this->textField('Téléphone principal', '+352 621 735 201', '.contact-detail:nth-child(2) a:first-of-type'),
                        $this->textField('Adresse', '1 Simmerfarm, L-8363 Simmerfarm, Luxembourg', '.contact-detail:nth-child(3) p', true),
                    ]),
                    $this->section('form', 'Formulaire', '.contact-form-wrap', [
                        $this->textField('Sur-titre', 'Envoyez-nous un message', '.contact-form-wrap .eyebrow'),
                        $this->textField('Titre', 'Nous vous répondons sous 24h.', '.contact-form-wrap h2'),
                        $this->buttonField('Bouton d’envoi', 'Envoyer', '.contact-form-wrap button[type="submit"]'),
                    ]),
                    $this->section('map', 'Adresse & carte', '.contact-map-section', [
                        $this->textField('Titre', 'Retrouvez-nous au Luxembourg.', '.contact-map-section h2'),
                        $this->textField('Adresse', '1 Simmerfarm, L-8363 Simmerfarm, Luxembourg', '.contact-map-copy address', true),
                        $this->buttonField('Lien Google Maps', 'Ouvrir dans Google Maps', '.contact-map-copy .text-link'),
                    ]),
                ],
            ],
        ];

        return $pages[$slug];
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
}
