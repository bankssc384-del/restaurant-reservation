# 🚀 Guide : Mises à jour automatiques via GitHub

Ce guide t'explique comment publier ton plugin sur GitHub pour que **les mises à jour s'installent en un clic** comme un plugin officiel WordPress.

---

## 📋 Ce que tu vas faire (vue d'ensemble)

1. Créer un compte GitHub (5 min, gratuit)
2. Créer un dépôt (repository) pour ton plugin
3. Uploader les fichiers du plugin
4. Modifier 2 lignes dans le code du plugin
5. Créer ta première "Release" (version officielle)
6. Tester la mise à jour automatique

**Temps total : 30 minutes** la première fois, puis 2 minutes pour chaque mise à jour future.

---

## ÉTAPE 1 — Créer un compte GitHub

1. Va sur **https://github.com/signup**
2. Entre ton e-mail professionnel
3. Choisis un mot de passe fort
4. Choisis un **nom d'utilisateur** simple (ex: `jardin-automne` ou `jean-dupont`) — il apparaîtra dans tes URLs
5. Vérifie ton e-mail
6. Choisis le plan **Free** (entièrement gratuit pour un plugin privé ou public)

✅ **Note bien ton nom d'utilisateur**, on en aura besoin à l'étape 4.

---

## ÉTAPE 2 — Créer le dépôt (repository)

1. Une fois connecté, en haut à droite, clique sur le **+** → **New repository**
2. Remplis le formulaire :
   - **Repository name** : `restaurant-reservation` (exactement ce nom, c'est important)
   - **Description** : "Plugin WordPress de réservation pour mon restaurant"
   - Coche **Public** (gratuit) OU **Private** (gratuit aussi, mais nécessite une config en plus pour les MAJ — on prend Public)
   - Coche **Add a README file**
3. Clique sur **Create repository**

✅ Ton dépôt est créé. URL : `https://github.com/TON-USERNAME/restaurant-reservation`

---

## ÉTAPE 3 — Uploader les fichiers du plugin

**Méthode simple, sans installer Git :**

1. Sur la page de ton nouveau dépôt, clique sur **Add file** → **Upload files**
2. **Glisse-dépose tous les fichiers du plugin** depuis ton ordinateur :
   - Ouvre le ZIP `restaurant-reservation.zip` que je t'ai fourni
   - Sélectionne **TOUT le contenu du dossier** `restaurant-reservation/` (PAS le dossier lui-même, juste l'intérieur)
   - Glisse les fichiers dans la zone de GitHub

3. En bas, mets un message de commit : "Première version"
4. Clique sur **Commit changes**

✅ Tu devrais voir maintenant la liste de tous tes fichiers : `restaurant-reservation.php`, dossiers `assets/`, `includes/`, `templates/`, etc.

---

## ÉTAPE 4 — Configurer ton username dans le code

Le plugin doit savoir quel dépôt GitHub regarder pour les mises à jour.

1. Sur GitHub, clique sur le fichier `includes/updater.php`
2. Clique sur l'icône **crayon ✏️** en haut à droite pour modifier
3. Cherche ces deux lignes (vers le début du fichier) :

```php
define( 'RR_GITHUB_USER', 'TON_USERNAME_GITHUB' );
define( 'RR_GITHUB_REPO', 'restaurant-reservation' );
```

4. Remplace `TON_USERNAME_GITHUB` par ton vrai nom d'utilisateur GitHub
   Exemple si ton username est `jardin-automne` :

```php
define( 'RR_GITHUB_USER', 'jardin-automne' );
define( 'RR_GITHUB_REPO', 'restaurant-reservation' );
```

5. En bas, clique sur **Commit changes** → **Commit changes**

✅ Le plugin sait maintenant où chercher les mises à jour.

---

## ÉTAPE 5 — Créer ta première Release

C'est cette étape qui rend une version "officielle" et déclenche les mises à jour.

1. Sur la page d'accueil de ton dépôt, regarde le menu de droite → tu vois **Releases**
2. Clique sur **Create a new release**
3. Remplis le formulaire :
   - **Choose a tag** : tape `v4.0.1` puis clique sur "Create new tag"
   - **Release title** : `Version 4.0.1`
   - **Description** : "Première version publique"
4. **Important** : tu dois aussi **uploader le ZIP** :
   - En bas, glisse-dépose le fichier `restaurant-reservation.zip` que je t'avais fourni
5. Clique sur **Publish release**

✅ La release est publiée. Tous les sites WordPress avec ce plugin pourront maintenant détecter cette version.

---

## ÉTAPE 6 — Installer le plugin sur ton site WordPress

1. Télécharge ton ZIP depuis la Release que tu viens de créer (ou réutilise celui que je t'ai fourni avec les corrections)
2. WordPress → Extensions → Ajouter → Téléverser une extension
3. Sélectionne le ZIP, installe, active

✅ Le plugin est installé avec le système de mise à jour intégré.

---

## 🎯 Comment publier une mise à jour à l'avenir

Quand tu (ou moi) modifies le plugin et qu'il y a une nouvelle version :

### 1. Modifier le numéro de version dans le code

Dans le fichier `restaurant-reservation.php`, en haut, change :

```php
* Version:     4.0.1
...
define( 'RR_VERSION',     '4.0.1' );
```

en par exemple :

```php
* Version:     4.1.0
...
define( 'RR_VERSION',     '4.1.0' );
```

⚠️ **Les deux endroits doivent avoir le même numéro.**

### 2. Uploader les fichiers modifiés sur GitHub

- Va dans ton dépôt → clique sur le fichier modifié → crayon ✏️ → colle le nouveau code → Commit
- OU re-glisse les nouveaux fichiers via "Upload files"

### 3. Créer une nouvelle Release

- Releases → Create a new release
- Tag : `v4.1.0` (DOIT correspondre au numéro de version dans le code)
- Title : `Version 4.1.0`
- Description : liste des changements (ex: "- Correction du bug X / - Ajout fonctionnalité Y")
- Glisse le nouveau ZIP en bas
- **Publish release**

### 4. La magie opère

Sur **tous les sites** où le plugin est installé, dans les **24h max** (généralement quelques heures), un badge rouge apparaît dans le menu **Extensions** indiquant "1 mise à jour disponible". Un clic sur "Mettre à jour" et c'est fini !

Pour forcer la vérification immédiate, va dans **Tableau de bord → Mises à jour → Vérifier de nouveau**.

---

## 📐 Convention de numérotation (Semantic Versioning)

Format : **MAJEUR.MINEUR.CORRECTIF**

- **4.0.0 → 4.0.1** : correctif de bug
- **4.0.1 → 4.1.0** : nouvelle fonctionnalité
- **4.1.0 → 5.0.0** : changement majeur (peut casser la compatibilité)

---

## ❓ Questions fréquentes

**Q : Et si quelqu'un d'autre voit mon code sur GitHub ?**
R : C'est normal, c'est public. Mais il n'y a aucune donnée sensible — pas de mots de passe, pas de clés API. Toutes les données privées sont stockées dans la base de chaque site séparément.

**Q : Puis-je rendre le dépôt privé ?**
R : Oui, mais le système de MAJ auto demande alors une clé API GitHub à configurer dans le plugin. Plus complexe — je peux le faire si besoin.

**Q : Combien de temps avant que la MAJ apparaisse sur les sites ?**
R : WordPress vérifie 1 fois toutes les 12h en général. Pour forcer : Tableau de bord → Mises à jour → "Vérifier de nouveau".

**Q : Et si je casse une MAJ ?**
R : Sur GitHub, tu peux supprimer la release problématique. Tu peux aussi publier rapidement une version `4.1.1` qui corrige le souci.

**Q : Puis-je revenir à une ancienne version ?**
R : Oui ! Dans ton dépôt → Releases → tu télécharges le ZIP de l'ancienne version → tu réinstalles manuellement.

---

## 🆘 En cas de problème

Si la mise à jour ne se déclenche pas :

1. Vérifie que `RR_GITHUB_USER` est bien rempli dans `includes/updater.php`
2. Vérifie que le tag de la release commence par `v` (ex: `v4.1.0`)
3. Vérifie que le numéro dans le tag correspond bien à `RR_VERSION` dans le code
4. Force la vérification : Tableau de bord → Mises à jour → "Vérifier de nouveau"
5. Vide le cache WordPress si tu as un plugin de cache

Si rien ne marche, dis-moi ce qui se passe et je t'aide à débuguer !
