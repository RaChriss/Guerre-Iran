-- ============================================
-- Base de données : Guerre Iran - Site d'information
-- ============================================

-- Suppression des tables existantes (dans l'ordre des dépendances)
DROP TABLE IF EXISTS articles CASCADE;

DROP TABLE IF EXISTS evenements CASCADE;

DROP TABLE IF EXISTS medias CASCADE;

DROP TABLE IF EXISTS pages CASCADE;

DROP TABLE IF EXISTS categories CASCADE;

DROP TABLE IF EXISTS administrateurs CASCADE;

DROP TABLE IF EXISTS configuration CASCADE;

-- ============================================
-- Table des administrateurs
-- ============================================
CREATE TABLE administrateurs (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    role VARCHAR(20) DEFAULT 'editeur' CHECK (role IN ('admin', 'editeur')),
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP
);

-- Index sur username pour les connexions
CREATE INDEX idx_administrateurs_username ON administrateurs (username);

-- ============================================
-- Table des catégories
-- ============================================
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    ordre INT DEFAULT 0,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index sur slug pour les URLs
CREATE INDEX idx_categories_slug ON categories (slug);

-- ============================================
-- Table des articles
-- ============================================
CREATE TABLE articles (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    chapeau TEXT,
    contenu TEXT NOT NULL,
    image_principale VARCHAR(255),
    alt_image VARCHAR(255),
    categorie_id INT REFERENCES categories (id) ON DELETE SET NULL,
    auteur_id INT REFERENCES administrateurs (id) ON DELETE SET NULL,
    statut VARCHAR(20) DEFAULT 'brouillon' CHECK (
        statut IN (
            'brouillon',
            'publie',
            'archive'
        )
    ),
    mise_en_avant BOOLEAN DEFAULT FALSE,
    date_publication TIMESTAMP,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    meta_titre VARCHAR(70),
    meta_description VARCHAR(160),
    meta_keywords VARCHAR(255),
    vues INT DEFAULT 0
);

-- Index pour les recherches fréquentes
CREATE INDEX idx_articles_slug ON articles (slug);

CREATE INDEX idx_articles_statut ON articles (statut);

CREATE INDEX idx_articles_categorie ON articles (categorie_id);

CREATE INDEX idx_articles_date_publication ON articles (date_publication DESC);

CREATE INDEX idx_articles_mise_en_avant ON articles (mise_en_avant)
WHERE
    mise_en_avant = TRUE;

-- ============================================
-- Table des médias
-- ============================================
CREATE TABLE medias (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    fichier VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    type VARCHAR(50) CHECK (type IN ('image', 'document')),
    taille INT,
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- Table des pages statiques
-- ============================================
CREATE TABLE pages (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    contenu TEXT NOT NULL,
    meta_titre VARCHAR(70),
    meta_description VARCHAR(160),
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index sur slug
CREATE INDEX idx_pages_slug ON pages (slug);

-- ============================================
-- Table de configuration
-- ============================================
CREATE TABLE configuration (
    cle VARCHAR(50) PRIMARY KEY,
    valeur TEXT,
    description VARCHAR(255)
);

-- ============================================
-- Table des événements chronologiques
-- ============================================
CREATE TABLE evenements (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    date_evenement DATE NOT NULL,
    image VARCHAR(255),
    alt_image VARCHAR(255),
    source VARCHAR(255),
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index sur date pour la chronologie
CREATE INDEX idx_evenements_date ON evenements (date_evenement DESC);

-- ============================================
-- DONNÉES INITIALES
-- ============================================

-- Administrateur par défaut (mot de passe: admin123)
-- Hash généré avec password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO
    administrateurs (
        username,
        password,
        email,
        nom,
        prenom,
        role,
        actif
    )
VALUES (
        'admin',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin@guerre-iran.com',
        'Administrateur',
        'Principal',
        'admin',
        TRUE
    );

-- Catégories par défaut
INSERT INTO
    categories (
        nom,
        slug,
        description,
        ordre,
        actif
    )
VALUES (
        'Contexte historique',
        'contexte-historique',
        'Articles sur l''histoire et les origines du conflit',
        1,
        TRUE
    ),
    (
        'Actualités',
        'actualites',
        'Dernières nouvelles et développements récents',
        2,
        TRUE
    ),
    (
        'Analyses géopolitiques',
        'analyses-geopolitiques',
        'Analyses approfondies des enjeux géopolitiques',
        3,
        TRUE
    ),
    (
        'Témoignages',
        'temoignages',
        'Témoignages de personnes affectées par le conflit',
        4,
        TRUE
    ),
    (
        'Impact humanitaire',
        'impact-humanitaire',
        'Articles sur les conséquences humanitaires',
        5,
        TRUE
    ),
    (
        'Relations internationales',
        'relations-internationales',
        'Réactions et positions de la communauté internationale',
        6,
        TRUE
    );

-- Configuration du site
INSERT INTO
    configuration (cle, valeur, description)
VALUES (
        'nom_site',
        'Guerre Iran - Informations',
        'Nom du site web'
    ),
    (
        'description_site',
        'Site d''information sur la situation en Iran',
        'Description générale du site'
    ),
    (
        'email_contact',
        'contact@guerre-iran.com',
        'Email de contact'
    ),
    (
        'articles_par_page',
        '10',
        'Nombre d''articles par page'
    ),
    (
        'facebook',
        '',
        'URL de la page Facebook'
    ),
    (
        'twitter',
        '',
        'URL du compte Twitter'
    ),
    (
        'instagram',
        '',
        'URL du compte Instagram'
    );

-- Pages statiques par défaut
INSERT INTO
    pages (
        titre,
        slug,
        contenu,
        meta_titre,
        meta_description,
        actif
    )
VALUES (
        'À propos',
        'a-propos',
        '# À propos de ce site

Ce site a pour vocation d''informer le public sur la situation actuelle en Iran, en fournissant des informations vérifiées et des analyses approfondies.

## Notre mission

Nous nous engageons à :
- Fournir des informations fiables et vérifiées
- Présenter différents points de vue
- Respecter la dignité des personnes
- Contextualiser les événements

## L''équipe

Notre équipe est composée de journalistes et d''analystes spécialisés dans la région du Moyen-Orient.',
        'À propos | Guerre Iran',
        'Découvrez notre mission et notre équipe dédiée à l''information sur la situation en Iran.',
        TRUE
    ),
    (
        'Contact',
        'contact',
        '# Contactez-nous

Pour toute question ou suggestion, n''hésitez pas à nous contacter.

## Formulaire de contact

Vous pouvez nous écrire à l''adresse : **contact@guerre-iran.com**

## Signaler une information

Si vous souhaitez nous transmettre une information ou un témoignage, veuillez nous contacter par email.',
        'Contact | Guerre Iran',
        'Contactez l''équipe du site Guerre Iran pour vos questions et suggestions.',
        TRUE
    ),
    (
        'Mentions légales',
        'mentions-legales',
        '# Mentions légales

## Éditeur du site

Ce site est édité dans le cadre d''un projet éducatif.

## Hébergement

Le site est hébergé sur une infrastructure Docker.

## Propriété intellectuelle

Tous les contenus présents sur ce site sont protégés par le droit d''auteur.

## Données personnelles

Ce site ne collecte aucune donnée personnelle sans votre consentement.',
        'Mentions légales | Guerre Iran',
        'Mentions légales et informations juridiques du site Guerre Iran.',
        TRUE
    );

-- Articles de démonstration
INSERT INTO
    articles (
        titre,
        slug,
        chapeau,
        contenu,
        categorie_id,
        auteur_id,
        statut,
        mise_en_avant,
        date_publication,
        meta_titre,
        meta_description
    )
VALUES (
        'Comprendre le contexte historique du conflit',
        'comprendre-contexte-historique-conflit',
        'Un aperçu des événements historiques qui ont façonné la situation actuelle en Iran.',
        '# Comprendre le contexte historique

## Les origines

Le conflit actuel trouve ses racines dans une histoire complexe qui remonte à plusieurs décennies...

## Les acteurs principaux

Plusieurs acteurs régionaux et internationaux sont impliqués dans cette situation...

## Les enjeux

Les enjeux sont multiples : géopolitiques, économiques, et humanitaires...

## Conclusion

Pour comprendre la situation actuelle, il est essentiel de prendre en compte ce contexte historique.',
        1,
        1,
        'publie',
        TRUE,
        CURRENT_TIMESTAMP,
        'Comprendre le contexte historique | Guerre Iran',
        'Découvrez les origines et le contexte historique du conflit en Iran.'
    ),
    (
        'Les derniers développements de la situation',
        'derniers-developpements-situation',
        'Point sur les événements récents et leurs implications.',
        '# Les derniers développements

## Cette semaine

Les événements de cette semaine ont marqué un tournant...

## Réactions internationales

La communauté internationale a réagi de diverses manières...

## Perspectives

Les analystes prévoient plusieurs scénarios possibles...',
        2,
        1,
        'publie',
        TRUE,
        CURRENT_TIMESTAMP,
        'Derniers développements | Guerre Iran',
        'Suivez les derniers développements de la situation en Iran.'
    ),
    (
        'Analyse : Les enjeux géopolitiques',
        'analyse-enjeux-geopolitiques',
        'Une analyse approfondie des enjeux géopolitiques régionaux et mondiaux.',
        '# Analyse des enjeux géopolitiques

## Le contexte régional

La région du Moyen-Orient est marquée par des tensions multiples...

## Les intérêts en jeu

Plusieurs puissances ont des intérêts dans cette région...

## Les alliances

Le système d''alliances est complexe et évolutif...

## Notre analyse

Cette situation nécessite une approche nuancée...',
        3,
        1,
        'publie',
        FALSE,
        CURRENT_TIMESTAMP,
        'Analyse géopolitique | Guerre Iran',
        'Analyse approfondie des enjeux géopolitiques liés à la situation en Iran.'
    );

-- Événements chronologiques de démonstration
INSERT INTO
    evenements (
        titre,
        description,
        date_evenement,
        source,
        actif
    )
VALUES (
        'Début des tensions',
        'Les premières tensions majeures apparaissent dans la région.',
        '2020-01-01',
        'Sources diplomatiques',
        TRUE
    ),
    (
        'Escalade régionale',
        'Une escalade significative des tensions est observée.',
        '2021-06-15',
        'Médias internationaux',
        TRUE
    ),
    (
        'Négociations diplomatiques',
        'Des pourparlers diplomatiques sont engagés.',
        '2022-03-20',
        'ONU',
        TRUE
    ),
    (
        'Situation humanitaire critique',
        'Les organisations humanitaires alertent sur la situation.',
        '2023-09-10',
        'UNHCR',
        TRUE
    );