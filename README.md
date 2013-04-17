qwik
====

Gestion d'un cms multilangue, sans base de données avec des fichiers yml :)

Faites un site "simple", en quelques minutes.



Modules dispos:
===

- File: Affichage d'un fichier html, twig, php, pour le reste, c'est du file_get_content
- Form: Envoi d'un mail via formulaire. Gestion automatique des champs text, email, date (avec range possible) et textarea
- Gallery: Gestion d'une galerie d'image récupérée à partir d'un ou plusieurs dossiers. Création automatique de thumbnail
- Gmaps: Affichage d'une carte gmaps avec plusieurs adresses/pin disponibles. Gestion de mouseover. Un point peut être une latitude/longitude, où une adresse donc la localisation va être calculée
- Html: Affichage d'html, directement intégré dans le fichier twig de la page (Module pour faire ses pages "quick'n'dirty" :) )
- Restaurant: Gestion d'une carte/menu pour restaurant

Librairies externes (BackEnd):
===

- Twig (Tout le temps)
- Swiftmailer (Envoi de formulaire)
- Yaml (Tout le temps, pour les fichiers config)
- Imagine (Création des thumbnails dans le module "gallery")


Librairies externes (FrontEnd):
===

- jQuery (Pour les modules "gallery" et "gmaps")
- jQueryUi (Pour le module "form", car il sert aux champs "date") => A changer
- Twitter bootstrap (pour le formulaire) => A changer
- Fancybox2 (pour la galerie)
- Google maps (Pour le module gmaps)


Updates:
===
- 26/01/2013: Utilisation de composer pour les vendor (yaml, swiftmailer, twig et Imagine)

La suite:
===


Il y a encore plein de choses à faire (voir les todos), à améliorer (Structure des dossiers/namespace), mais voici ce qu'il reste au programme:

- Faire du unit testing
- Utiliser Kalendae plutot que datepicker
- Pas utiliser bootstrap pour le formulaire
- Utiliser openSaas
- Utiliser coffeescript
- minify des fichiers statiques + versionning des asserts, comme ca on peut faire du cache des fichiers sans avoir "peur" lors de la MTP (voir webpagetest.org)
- Gestion des sous(-sous)-domaines, pour les fichiers statiques pour améliorer les perfs front-end, avec CDN?
- Utilisation de la classe "Config", au lieu d'array, là où ce n'est pas déjà fait
- Faire un cache de la config (et pages) yml en php, pour ne pas tout recharger à chaque fois (voir var_export) et possibilité de "clearer" ce cache avec le admin/cc
- Connexion sur le site en mode admin via oauth (choix entre facebook, google, twitter, etc...). Pour cela, utiliser un site passerelle, comme ca on doit pas ajouter le domaine dans les configs des sites. Pour cela, il faudra donc faire un session_handler et peut-être utiliser des fichiers txt pour que ce soit "cross domain"
- Peut-être donner la possibilité d'appeler un Module dans un autre Module.. A voir
- Spinner de gmaps, pendant qu'on charge les adresses
- Création d'un site via une interface. Pour cela il faut que l'authentification soit déjà faite :)
- Admin: Gestion des pages et donc aussi des modules
- Admin: Si on permet de changer des choses, préparer le "versionning", comme ca en cas de problème on peut remettre une version précédente
- Faire un dossier /public que l'on mettrait dans /sites/monsite.com/public et sur lequel on pourrait faire un publish pour mettre tout dans www. Comme ca un site n'est pas "divisé" en 2 (config + www) et peut donc être zippés. Mais c'est un peu long, car en dev, il faut que les fichiers soit pris d'un dossier qui n'est pas publique. Il faut donc bien y penser pour ne pas qu'il y ai de faille :)
- Peut-être gérer les exceptions non catchées via le set-exception-handler. Comme ca on doit pas faire un gros "try/catch" de "render" (il se peut qu'il y ai des exceptions avant le render, dans l'init par exemple)