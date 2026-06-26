-- ============================================================
-- SCOUT APP — Script de création de la base de données
-- Moteur : MySQL 8.0+ | Charset : utf8mb4
-- À exécuter dans phpMyAdmin (onglet SQL)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `scout_app`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `scout_app`;

-- ============================================================
-- Désactiver temporairement les FK pour l'insertion propre
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;


-- ─────────────────────────────────────────────────────────────
-- 1. VILLES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `villes` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom`        VARCHAR(150)    NOT NULL,
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `villes_nom_unique` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 2. GRADES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `grades` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom`        VARCHAR(100)    NOT NULL,
    `niveau`     INT             NOT NULL DEFAULT 1,
    `image`      VARCHAR(255)    NULL DEFAULT NULL,
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 3. USERS
-- (dépend de villes, grades — regiments/groups référencés plus tard)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom`               VARCHAR(100)    NOT NULL,
    `prenom`            VARCHAR(100)    NOT NULL,
    `email`             VARCHAR(191)    NOT NULL,
    `email_verified_at` TIMESTAMP       NULL DEFAULT NULL,
    `telephone`         VARCHAR(20)     NULL DEFAULT NULL,
    `password`          VARCHAR(255)    NOT NULL,
    `profile_pic`       VARCHAR(255)    NULL DEFAULT NULL,
    `role`              ENUM('admin','chef_regiment','chef_groupe','candidat')
                                        NOT NULL DEFAULT 'candidat',
    `ville_id`          BIGINT UNSIGNED NOT NULL,
    `regiment_id`       BIGINT UNSIGNED NULL DEFAULT NULL,
    `group_id`          BIGINT UNSIGNED NULL DEFAULT NULL,
    `grade_id`          BIGINT UNSIGNED NULL DEFAULT NULL,
    `remember_token`    VARCHAR(100)    NULL DEFAULT NULL,
    `created_at`        TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`        TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_ville_id_foreign`   (`ville_id`),
    KEY `users_grade_id_foreign`   (`grade_id`),
    -- regiment_id et group_id : FK ajoutées après création des tables correspondantes
    KEY `users_regiment_id_index`  (`regiment_id`),
    KEY `users_group_id_index`     (`group_id`),
    CONSTRAINT `users_ville_id_foreign`
        FOREIGN KEY (`ville_id`) REFERENCES `villes` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `users_grade_id_foreign`
        FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 4. REGIMENTS
-- (dépend de villes et users pour chef_id)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `regiments` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom`        VARCHAR(150)    NOT NULL,
    `ville_id`   BIGINT UNSIGNED NOT NULL,
    `chef_id`    BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `regiments_ville_id_foreign` (`ville_id`),
    KEY `regiments_chef_id_foreign`  (`chef_id`),
    CONSTRAINT `regiments_ville_id_foreign`
        FOREIGN KEY (`ville_id`) REFERENCES `villes`  (`id`) ON DELETE RESTRICT,
    CONSTRAINT `regiments_chef_id_foreign`
        FOREIGN KEY (`chef_id`)  REFERENCES `users`   (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 5. GROUPS
-- (dépend de regiments et users)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `groups` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom`          VARCHAR(150)    NOT NULL,
    `regiment_id`  BIGINT UNSIGNED NOT NULL,
    `chef_id`      BIGINT UNSIGNED NULL DEFAULT NULL,
    `assistant_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at`   TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`   TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `groups_regiment_id_foreign`  (`regiment_id`),
    KEY `groups_chef_id_foreign`      (`chef_id`),
    KEY `groups_assistant_id_foreign` (`assistant_id`),
    CONSTRAINT `groups_regiment_id_foreign`
        FOREIGN KEY (`regiment_id`)  REFERENCES `regiments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `groups_chef_id_foreign`
        FOREIGN KEY (`chef_id`)      REFERENCES `users`     (`id`) ON DELETE SET NULL,
    CONSTRAINT `groups_assistant_id_foreign`
        FOREIGN KEY (`assistant_id`) REFERENCES `users`     (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- Ajout des FK différées sur users (regiment_id, group_id)
-- ─────────────────────────────────────────────────────────────
ALTER TABLE `users`
    ADD CONSTRAINT `users_regiment_id_foreign`
        FOREIGN KEY (`regiment_id`) REFERENCES `regiments` (`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `users_group_id_foreign`
        FOREIGN KEY (`group_id`)    REFERENCES `groups`    (`id`) ON DELETE SET NULL;


-- ─────────────────────────────────────────────────────────────
-- 6. GROUP APPLICATIONS (Candidatures)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `group_applications` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `group_id`   BIGINT UNSIGNED NOT NULL,
    `statut`     ENUM('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `group_applications_user_id_foreign`  (`user_id`),
    KEY `group_applications_group_id_foreign` (`group_id`),
    CONSTRAINT `group_applications_user_id_foreign`
        FOREIGN KEY (`user_id`)  REFERENCES `users`  (`id`) ON DELETE CASCADE,
    CONSTRAINT `group_applications_group_id_foreign`
        FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 7. CATEGORIES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom`        VARCHAR(100)    NOT NULL,
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `categories_nom_unique` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 8. SONGS (Chansons)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `songs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titre`      VARCHAR(200)    NOT NULL,
    `paroles`    LONGTEXT        NULL DEFAULT NULL,
    `audio_url`  VARCHAR(255)    NULL DEFAULT NULL,
    `categorie`  VARCHAR(100)    NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `songs_created_by_foreign` (`created_by`),
    CONSTRAINT `songs_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 9. GUIDES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `guides` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titre`        VARCHAR(200)    NOT NULL,
    `contenu_html` LONGTEXT        NOT NULL,
    `cover_image`  VARCHAR(255)    NULL DEFAULT NULL,
    `category_id`  BIGINT UNSIGNED NOT NULL,
    `created_by`   BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at`   TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`   TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `guides_category_id_foreign` (`category_id`),
    KEY `guides_created_by_foreign`  (`created_by`),
    CONSTRAINT `guides_category_id_foreign`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `guides_created_by_foreign`
        FOREIGN KEY (`created_by`)  REFERENCES `users`      (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 10. EVENTS (Événements)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `events` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titre`       VARCHAR(200)    NOT NULL,
    `description` TEXT            NULL DEFAULT NULL,
    `date_debut`  DATETIME        NOT NULL,
    `date_fin`    DATETIME        NOT NULL,
    `lieu`        VARCHAR(255)    NULL DEFAULT NULL,
    `latitude`    DECIMAL(10,7)   NULL DEFAULT NULL,
    `longitude`   DECIMAL(10,7)   NULL DEFAULT NULL,
    `cover_image` VARCHAR(255)    NULL DEFAULT NULL,
    `type`        ENUM('ville','regiment') NOT NULL DEFAULT 'ville',
    `ville_id`    BIGINT UNSIGNED NOT NULL,
    `regiment_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_by`  BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at`  TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `events_ville_id_foreign`    (`ville_id`),
    KEY `events_regiment_id_foreign` (`regiment_id`),
    KEY `events_created_by_foreign`  (`created_by`),
    CONSTRAINT `events_ville_id_foreign`
        FOREIGN KEY (`ville_id`)    REFERENCES `villes`    (`id`) ON DELETE RESTRICT,
    CONSTRAINT `events_regiment_id_foreign`
        FOREIGN KEY (`regiment_id`) REFERENCES `regiments` (`id`) ON DELETE SET NULL,
    CONSTRAINT `events_created_by_foreign`
        FOREIGN KEY (`created_by`)  REFERENCES `users`     (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 11. ACTIVITIES (Activités de groupe)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `activities` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_id`   BIGINT UNSIGNED NOT NULL,
    `titre`      VARCHAR(200)    NOT NULL,
    `programme`  TEXT            NULL DEFAULT NULL,
    `date`       DATE            NOT NULL,
    `lieu`       VARCHAR(255)    NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `activities_group_id_foreign`   (`group_id`),
    KEY `activities_created_by_foreign` (`created_by`),
    CONSTRAINT `activities_group_id_foreign`
        FOREIGN KEY (`group_id`)   REFERENCES `groups` (`id`) ON DELETE CASCADE,
    CONSTRAINT `activities_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 12. ATTENDANCES (Pointage / Présences — polymorphique)
-- attendable_type : 'App\Models\Event' ou 'App\Models\Activity'
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `attendances` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`          BIGINT UNSIGNED NOT NULL,
    `attendable_type`  VARCHAR(191)    NOT NULL,
    `attendable_id`    BIGINT UNSIGNED NOT NULL,
    `statut`           ENUM('present','absent','en_attente') NOT NULL DEFAULT 'en_attente',
    `date_pointage`    DATE            NOT NULL,
    `created_at`       TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`       TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    -- Index composite pour la relation polymorphique
    KEY `attendances_attendable_index` (`attendable_type`, `attendable_id`),
    KEY `attendances_user_id_foreign`  (`user_id`),
    -- Index pour éviter les doublons de pointage
    UNIQUE KEY `attendances_unique_pointage`
        (`user_id`, `attendable_type`, `attendable_id`, `date_pointage`),
    CONSTRAINT `attendances_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 13. PERSONAL ACCESS TOKENS (Laravel Sanctum)
-- Créée par : php artisan install:api
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tokenable_type` VARCHAR(255)    NOT NULL,
    `tokenable_id`   BIGINT UNSIGNED NOT NULL,
    `name`           VARCHAR(255)    NOT NULL,
    `token`          VARCHAR(64)     NOT NULL,
    `abilities`      TEXT            NULL DEFAULT NULL,
    `last_used_at`   TIMESTAMP       NULL DEFAULT NULL,
    `expires_at`     TIMESTAMP       NULL DEFAULT NULL,
    `created_at`     TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
    KEY `personal_access_tokens_tokenable_type_tokenable_id_index`
        (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 14. TABLE DE SUIVI DES MIGRATIONS (Laravel interne)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `migrations` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch`     INT          NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- TABLE DE CACHE (optionnelle — php artisan cache:table)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cache` (
    `key`        VARCHAR(255) NOT NULL,
    `value`      MEDIUMTEXT   NOT NULL,
    `expiration` INT          NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
    `key`        VARCHAR(255) NOT NULL,
    `owner`      VARCHAR(255) NOT NULL,
    `expiration` INT          NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Réactiver les vérifications FK
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================
-- DONNÉES INITIALES (Admin par défaut)
-- Mot de passe : "password" hashé avec bcrypt
-- À modifier après le premier accès !
-- ============================================================

INSERT INTO `villes` (`nom`, `created_at`, `updated_at`)
VALUES ('Ville par défaut', NOW(), NOW());

INSERT INTO `users`
    (`nom`, `prenom`, `email`, `password`, `role`, `ville_id`, `created_at`, `updated_at`)
VALUES (
    'Admin',
    'Scout',
    'admin@scoutapp.com',
    '$2y$12$eGqBNNQqDNfOVFsKDq8VXO6.4CfGqiVDtJmEFNXB6UivJJ2UzJOhm', -- password
    'admin',
    1,
    NOW(),
    NOW()
);

-- ============================================================
-- FIN DU SCRIPT
-- ============================================================
