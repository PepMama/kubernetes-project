# README

## Objectif du projet

Construire une mini plateforme de recettes déployable sur Kubernetes avec une architecture multi-services cohérente :

- frontend Angular ;
- API Symfony (lecture + création) ;
- MySQL ;
- reverse proxy Nginx ;
- service média (upload) avec volume persistant.

## Architecture

Services déployés dans le namespace `recipes-app` :

- `nginx` : point d'entrée exposé en `NodePort` ;
- `front` : application Angular ;
- `api-public` : Symfony pour les routes de lecture ;
- `api-admin` : Symfony pour les routes d'écriture ;
- `mysql` : base de données ;
- `upload` : service média.

## BEsoins du TP :

- minimum 5 pods (ici 6 pods, 1 conteneur/pod) ;
- plusieurs configurations réseau (`NodePort` + `ClusterIP`) ;
- au moins 2 volumes (`mysql-pvc`, `upload-pvc`) ;
- au moins 2 configurations surchargées (`nginx-config`, `mysql-secret`, `app-demo-pages`).

## Commandes de déploiement

Depuis la racine du projet :

```bash
docker build -t recipes-symfony:latest ./backend
docker build -t recipes-angular:latest ./frontend
kubectl apply -f .
```

Rollout manuel si nécessaire :

```bash
kubectl rollout restart deployment/nginx-deployment -n recipes-app
kubectl rollout restart deployment/front-deployment -n recipes-app
kubectl rollout restart deployment/api-public-deployment -n recipes-app
kubectl rollout restart deployment/api-admin-deployment -n recipes-app
```

## Commandes de vérification

```bash
kubectl get all -n recipes-app
kubectl get pvc -n recipes-app
kubectl get svc -n recipes-app
kubectl get configmap -n recipes-app
kubectl get secret -n recipes-app
kubectl logs -n recipes-app deploy/nginx-deployment
```

## URLs de démonstration

- Frontend : http://localhost:30080/
- API publique : http://localhost:30080/api/
- GET all recettes : http://localhost:30080/api/recipes
- API admin : http://localhost:30080/admin/
- CREATE recette (`POST`) : http://localhost:30080/admin/recipes
- Média : http://localhost:30080/media/

Exemple `POST` :

```bash
curl -X POST http://localhost:30080/admin/recipes \
	-H 'Content-Type: application/json' \
	-d '{"title":"Quiche lorraine","description":"Lardons, crème, oeufs"}'
```

## Important

Utiliser `localhost:30080` (et non `localhost:80`) pour éviter le conflit avec Apache/local web server.
