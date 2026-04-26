# README

## Objectif du projet

Ce projet présente une architecture multi-services déployable sur Kubernetes pour une plateforme de recettes nommée `recipes-app`.

L'objectif est de démontrer :

- le déploiement de plusieurs services cohérents ;
- l'exposition réseau d'une plateforme via un point d'entrée Nginx ;
- l'utilisation de volumes persistants ;
- l'utilisation de configurations surchargées avec `ConfigMap` et `Secret` ;
- une base prête à accueillir des images applicatives réelles.

Dans cette version de démonstration, les services applicatifs `front`, `api-public`, `api-admin` et `upload` utilisent des pages HTML de test afin de valider le routage et l'architecture réseau.

---

## Architecture

### Vue d'ensemble

La plateforme est composée des services suivants :

- `nginx` : point d'entrée principal, reverse proxy exposé en `NodePort` ;
- `front` : service frontend de démonstration ;
- `api-public` : service API publique de démonstration ;
- `api-admin` : service API d'administration de démonstration ;
- `upload` : service média avec stockage persistant ;
- `mysql` : base de données MySQL avec stockage persistant.

### Namespace

Toutes les ressources sont déployées dans le namespace :

- `recipes-app`

### Réseau

- `nginx-service` expose la plateforme sur le port `30080` ;
- `front-service`, `api-public-service`, `api-admin-service`, `upload-service` et `mysql-service` sont exposés en `ClusterIP` ;
- le routage est assuré par `nginx-config`.

### Volumes

Deux volumes persistants sont utilisés :

- `mysql-pvc` : stockage des données MySQL ;
- `upload-pvc` : stockage du service média.

### Configurations surchargées

Deux configurations surchargées sont utilisées :

- `nginx-config` : configuration du reverse proxy ;
- `app-demo-pages` : pages HTML de démonstration injectées dans les pods ;
- `mysql-secret` : secret contenant les identifiants MySQL.

---

## Commandes de déploiement

Depuis le dossier du projet :

```bash
kubectl apply -f .
```

Si nécessaire, redémarrer les déploiements après modification des configurations :

```bash
kubectl rollout restart deployment/nginx-deployment -n recipes-app
kubectl rollout restart deployment/front-deployment -n recipes-app
kubectl rollout restart deployment/api-public-deployment -n recipes-app
kubectl rollout restart deployment/api-admin-deployment -n recipes-app
kubectl rollout restart deployment/upload-deployment -n recipes-app
```

Attendre que les déploiements soient prêts :

```bash
kubectl rollout status deployment/nginx-deployment -n recipes-app
kubectl rollout status deployment/front-deployment -n recipes-app
kubectl rollout status deployment/api-public-deployment -n recipes-app
kubectl rollout status deployment/api-admin-deployment -n recipes-app
kubectl rollout status deployment/upload-deployment -n recipes-app
kubectl rollout status deployment/mysql-deployment -n recipes-app
```

---

## Commandes de vérification

Vérifier l'ensemble des ressources :

```bash
kubectl get all -n recipes-app
```

Vérifier les volumes persistants :

```bash
kubectl get pvc -n recipes-app
```

Vérifier les configurations :

```bash
kubectl get configmap -n recipes-app
kubectl get secret -n recipes-app
```

Vérifier les services :

```bash
kubectl get svc -n recipes-app
```

Consulter les logs du reverse proxy :

```bash
kubectl logs -n recipes-app deploy/nginx-deployment
```

Afficher la configuration Nginx chargée dans le pod :

```bash
kubectl exec -n recipes-app deploy/nginx-deployment -- cat /etc/nginx/nginx.conf
```

---

## URLs de démonstration

Point d'entrée de la plateforme :

- http://localhost:30080/

Routes disponibles :

- Frontend : http://localhost:30080/
- API publique : http://localhost:30080/api/
- API admin : http://localhost:30080/admin/
- Média : http://localhost:30080/media/

### Important

Le port `80` de la machine locale peut être utilisé par Apache ou un autre serveur local.

Pour cette raison, les URLs correctes pour la démonstration sont celles utilisant le port `30080`.
