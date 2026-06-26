# Cahier de Charges — Application Mobile Scout

## 1. Contexte et présentation du projet

L'application a pour objectif de digitaliser la gestion et la vie quotidienne d'un mouvement scout structuré hiérarchiquement : **Ville → Régiments → Groupes → Candidats**. Elle s'adresse à 4 types d'acteurs (admin, chef de régiment, chef de groupe, candidat) pour centraliser les ressources pédagogiques (chants, guides), la gestion des événements et activités, le suivi de présence, et à terme la communication interne (chat) et des fonctionnalités enrichies par l'IA (gamification, badges).

**Stack technique :**
- **Frontend mobile :** Flutter (state management : Provider)
- **Communication API :** package `http`
- **Backend :** Laravel 11 (API REST)
- **Base de données :** MySQL
- **Persistance locale :** `shared_preferences` (token, données légères, cache)
- **Authentification :** Laravel Sanctum (formulaire email/mot de passe) + perspective SSO (Google)
- **Cartes :** OpenStreetMap (ex: `flutter_map`)

---

## 2. Objectifs du projet

- Centraliser l'information du mouvement scout (villes, régiments, groupes, grades).
- Gérer le processus de candidature des scouts aux groupes.
- Fournir une bibliothèque de contenus pédagogiques (chants, guides) organisée par catégorie, gérée par l'admin.
- Gérer un agenda d'événements (ville / régiment) et des activités propres à chaque groupe.
- Tenir un carnet de présence pour les événements et les activités.
- Offrir une expérience offline-first pour les zones à faible connectivité.
- Préparer le terrain pour des évolutions futures : gamification (badges), chat, IA.

---

## 3. Acteurs / Profils utilisateurs

| Rôle | Description |
|---|---|
| **Admin** | Gère les villes/régiments, les contenus pédagogiques (guides, chants), supervision globale |
| **Chef de régiment** | Crée les groupes de son régiment et affecte leur chef de groupe |
| **Chef de groupe** | Choisit et ajoute son assistant, gère les membres (candidats), crée les activités de son groupe, valide les candidatures, pointe la présence |
| **Candidat (user simple)** | Postule à un groupe, consulte guides/chants/événements de sa ville, participe aux activités de son groupe |

Chaque utilisateur est caractérisé par : nom, prénom, email, téléphone, mot de passe, ville, régiment (si affecté), groupe (si affecté), grade, photo de profil.

---

## 4. Architecture organisationnelle (hiérarchie)

```
Ville
 └── Régiment (chef de régiment)
      └── Groupe (chef de groupe + assistant)
           └── Candidat (Grade : Louveteau / Scout / Éclaireur / Chef…)
```

- Une **Ville** contient plusieurs **Régiments**.
- Un **Régiment** contient plusieurs **Groupes**, et a un **chef de régiment**.
- Un **Groupe** appartient à un seul Régiment, a un **chef de groupe** et un **assistant** (tous deux choisis/affectés, pas auto-désignés).
- Un **Candidat** appartient à une Ville (toujours), et — une fois sa candidature acceptée — à un Régiment et un Groupe.
- Un candidat **ne voit que les événements de sa propre ville**.

---

## 5. Modules fonctionnels (Features)

### 5.1 Authentification & Profil
- Inscription / Connexion par formulaire (email + mot de passe)
- Récupération de mot de passe
- Authentification via Laravel Sanctum (token)
- Profil utilisateur : nom, prénom, email, téléphone, photo, ville, régiment, groupe, grade
- *Perspective :* connexion SSO (Google)

### 5.2 Gestion de la structure organisationnelle
- CRUD Ville / Régiment (réservé à l'admin)
- Création de Groupes et affectation du chef de groupe — réservé au **chef de régiment**
- Gestion des grades (Louveteau, Scout, Éclaireur, Chef…)

### 5.3 Candidature aux groupes
- Le candidat consulte la liste des groupes de sa ville (via les régiments de sa ville)
- Le candidat postule à un groupe (`GroupApplication` : en_attente)
- Le chef de groupe consulte les candidatures reçues et les accepte/refuse
- À l'acceptation, le candidat est rattaché au groupe (et au régiment correspondant)

### 5.4 Gestion des membres d'un groupe
- Le chef de groupe choisit et ajoute son **assistant** (parmi les membres ou les candidats acceptés)
- Le chef de groupe gère la liste des membres (candidats) de son groupe : voir, retirer, changer de grade

### 5.5 Chants (Songs)
- Liste des chants par catégorie
- Détail d'un chant : paroles (texte) + fichier audio (lecture intégrée, player avec play/pause/seek)
- Recherche et favoris
- Ajout/gestion réservés à l'**admin**

### 5.6 Guides pédagogiques (Cours)
- Organisés par **catégorie** (ex : montage de tente, premiers secours, nœuds, drapeau, feu de camp)
- Éditeur **WYSIWYG** (type Flutter Quill) côté création : titres, paragraphes, images, vidéos
- Contenu stocké en **HTML** côté backend
- Images uploadées → converties/stockées sur serveur et insérées en `<img>` dans le HTML
- Vidéos : upload ou lien YouTube embarqué
- Lecture côté app : rendu HTML natif (ex: `flutter_html`)
- Ajout/gestion réservés à l'**admin**

### 5.7 Agenda & Événements
- Agenda centralisant tous les événements visibles par l'utilisateur
- Deux types d'événements :
  - **Événements de ville** (généraux) — visibles par tous les membres de la ville
  - **Événements de régiment** — visibles par les membres du régiment concerné (et toujours dans la même ville)
- Un **candidat ne voit que les événements de sa ville** (généraux + ceux de son régiment si applicable)
- Détail : titre, description, lieu (carte), date/heure, image de couverture
- Création réservée à l'admin (ville) et au chef de régiment (régiment)

### 5.8 Activités de groupe
- Chaque **groupe a ses propres activités** (programme, date, lieu)
- Création et gestion réservées au **chef de groupe** (et son assistant)
- Visibles uniquement par les membres du groupe concerné

### 5.9 Carnet de présence
- Présence pointée pour :
  - les **événements** (ville/régiment) — par l'organisateur (admin/chef de régiment)
  - les **activités de groupe** — par le chef de groupe/assistant
- Statuts : présent / absent / en attente
- Historique de présence consultable par candidat (son propre carnet) et par les chefs (vue d'ensemble du groupe)

### 5.10 Cartes (Maps)
- Visualisation des lieux d'événements/activités sur OpenStreetMap
- Itinéraire vers le lieu d'un événement/activité

### 5.11 Gamification — perspective future
- **Badges** : catalogue, attribution par les chefs/admin, affichage sur le profil
- **Chat** : messagerie de groupe/régiment, perspective future
- Système de points liés aux présences/activités complétées

### 5.12 Intégration IA — perspective future
- Chatbot d'assistance (réponses sur les guides, FAQ scout)
- Recommandation personnalisée de guides selon grade/progression
- Résumé automatique de guides longs

---

## 6. Exigences non-fonctionnelles (en lien avec les critères d'évaluation)

### 6.1 Architecture et qualité du code
- **State management :** Provider partout (ChangeNotifier + Consumer/Selector), pas de setState pour la logique métier
- **Structure de dossiers Flutter :**
```
lib/
 ├── core/
 │    ├── constants/
 │    ├── network/
 │    ├── services/
 │    ├── theme/
 │    └── utils/
 ├── models/
 ├── features/
 │    ├── auth/
 │    ├── profile/
 │    ├── songs/
 │    ├── guides/
 │    ├── events/
 │    ├── maps/
 │    ├── badges/
 │    └── chat/
 ├── providers/
 ├── widgets/
 └── main.dart
```
Chaque sous-dossier de `features/` contient ses propres widgets/écrans spécifiques ; les modèles partagés restent dans `models/`, les providers globaux (ou un provider par feature) dans `providers/`, et les widgets génériques réutilisables (boutons, loaders, cards) dans `widgets/`. Les nouvelles features (structure organisationnelle, candidature, activités, carnet de présence) suivent le même découpage (ex: `features/groups/`, `features/applications/`, `features/activities/`, `features/attendance/`).
- **Clean code :** conventions Dart (lowerCamelCase, fichiers snake_case), pas de duplication, widgets réutilisables (boutons, cards, loaders)
- **Gestion des rôles côté Flutter :** un provider `AuthProvider` expose le rôle courant (`admin/chef_regiment/chef_groupe/candidat`) pour conditionner l'affichage des écrans/actions (ex: bouton "Créer un groupe" visible uniquement pour `chef_regiment`)
- **Gestion des erreurs :** tout est géré manuellement avec le package `http` :
  - Timeout via `.timeout(Duration(seconds: 10))` sur chaque requête
  - Détection de connexion via `connectivity_plus` avant l'appel
  - Classe `ApiException` centralisée (catch `SocketException`, `TimeoutException`, codes HTTP ≥ 400)
  - État `idle/loading/success/error` dans chaque Provider
  - Affichage : Snackbar pour erreurs ponctuelles, `MaterialBanner` persistant pour "pas de connexion", écran avec bouton "Réessayer" pour les échecs de chargement initial, `Dialog` pour erreurs bloquantes (session expirée)

### 6.2 UI/UX
- Thème Material centralisé (couleurs, typographie scout)
- Responsive (différentes tailles d'écran)
- Loading spinners, Snackbars succès/erreur, transitions/animations fluides
- États vides (empty states) gérés (ex : aucun événement, aucune candidature, aucun chant)

### 6.3 Intégration technique & données
- API REST Laravel (Sanctum pour l'auth, Resources pour le formatage JSON)
- Consommation API via package `http` + repository pattern
- Persistance :
  - **Online :** source de vérité (Laravel + MySQL)
  - **Offline :** `shared_preferences` pour le cache léger (token, dernières données consultées sérialisées en JSON : liste de chants/guides déjà chargés, agenda, profil) → consultation hors-ligne basique, synchronisation à la reconnexion
  - Justification : contenu pédagogique et agenda doivent rester accessibles sans connexion (terrain, camps scouts) ; `shared_preferences` suffit pour des volumes raisonnables (texte/JSON), à réévaluer vers une base locale (SQLite/Hive) si le volume de cache devient important
- Visualisation de données : listes, cartes (OpenStreetMap), statistiques de présence (graphiques simples)

### 6.4 Travail sans migrations Laravel
- Création des tables via SQL brut / phpMyAdmin / MySQL Workbench (schéma figé en amont)
- Models Eloquent classiques pointant vers les tables existantes (`protected $table`)
- Avantage : rapide pour un projet solo/démo ; limite : pas d'historique de schéma ni de rollback automatisé — à réévaluer si déploiement en production avec évolutions fréquentes

---

## 7. Modèle de données (entités principales)

- **Ville** (id, nom)
- **Regiment** (id, nom, ville_id, chef_id)
- **Group** (id, nom, regiment_id, chef_id, assistant_id)
- **Grade** (id, nom, niveau, image)
- **User** (id, nom, prénom, email, telephone, password, profile_pic, role, ville_id, regiment_id, group_id, grade_id)
- **GroupApplication** (id, user_id, group_id, statut[en_attente/acceptee/refusee], created_at)
- **Category** (id, nom)
- **SongCategory** (id, nom)
- **Song** (id, titre, paroles, audio_url, song_category_id, created_by)
- **Guide** (id, titre, contenu_html, cover_image, category_id, created_by)
- **Event** (id, titre, description, date_debut, date_fin, lieu, latitude, longitude, cover_image, type[ville/regiment], ville_id, regiment_id nullable, created_by)
- **Activity** (id, group_id, titre, programme, date, lieu, created_by)
- **Attendance** (id, user_id, attendable_type[Event/Activity], attendable_id, statut[present/absent/en_attente], date_pointage)
- *(Perspective future)* **Badge**, **UserBadge**, **Conversation**, **Message**

---

## 8. API Backend (endpoints principaux — exemples)

```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/profile

GET    /api/villes
GET    /api/regiments?ville_id=
POST   /api/regiments               (admin)
GET    /api/groups?regiment_id=
POST   /api/groups                  (chef_regiment)
PATCH  /api/groups/{id}/chef        (chef_regiment - affecter chef de groupe)
PATCH  /api/groups/{id}/assistant   (chef_groupe - affecter assistant)

POST   /api/applications            (candidat - postuler à un groupe)
GET    /api/applications?group_id=  (chef_groupe - voir candidatures reçues)
PATCH  /api/applications/{id}       (chef_groupe - accepter/refuser)

GET    /api/songs
GET    /api/songs/{id}

GET    /api/categories
GET    /api/guides?category_id=
GET    /api/guides/{id}
POST   /api/guides                  (admin)
POST   /api/guides/upload-image
POST   /api/guides/upload-video

GET    /api/events                  (filtré automatiquement par ville_id de l'utilisateur)
GET    /api/events/{id}
POST   /api/events                  (admin/chef_regiment)

GET    /api/activities?group_id=
POST   /api/activities              (chef_groupe)

POST   /api/attendance              (pointer une présence : attendable_type, attendable_id, user_id, statut)
GET    /api/attendance?user_id=     (carnet de présence d'un candidat)
GET    /api/attendance?activity_id= (carnet d'une activité)
```

---

## 9. Découpage en phases (roadmap suggérée)

| Phase | Contenu |
|---|---|
| **Phase 1 — Fondations** | Auth (Sanctum), profil, structure organisationnelle (Ville/Régiment/Groupe), thème, gestion d'erreurs |
| **Phase 2 — Candidature & Groupes** | Candidature aux groupes, gestion des membres/assistant par chef de groupe |
| **Phase 3 — Contenus** | Chants, Guides par catégorie (éditeur WYSIWYG + lecture HTML), gérés par l'admin |
| **Phase 4 — Agenda & Activités** | Événements ville/régiment, activités de groupe, cartes OpenStreetMap |
| **Phase 5 — Présence** | Carnet de présence (événements + activités) |
| **Phase 6 — Perspectives** | Gamification (badges, points), chat, SSO, notifications push, IA |

---

## 10. Livrables attendus

- Application Flutter fonctionnelle (APK) connectée à l'API
- API Laravel 11 documentée (Postman/Swagger)
- Base de données MySQL (schéma SQL, sans migrations)
- Documentation technique (architecture, choix de persistance, justification Provider)
- Présentation/démo couvrant les critères d'évaluation (architecture, UI/UX, intégration technique)
