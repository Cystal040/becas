#!/bin/bash

<<<<<<< HEAD
PROJECT_DIR="/c/Users/Josue/Documents/becas"
=======
# Ruta de tu proyecto
PROJECT_DIR="/c/Users/Administrator/Documents/becas"
>>>>>>> abcd1752c099e47b57e14b995621f49bec1bd52b
BRANCH="main"

cd "$PROJECT_DIR" || exit

echo "🔄 Auto Sync iniciado en $PROJECT_DIR (rama $BRANCH)..."

while true; do
    echo "📥 Descargando cambios remotos..."
    git pull origin $BRANCH --allow-unrelated-histories --no-edit

    if ! git diff --quiet || ! git diff --cached --quiet; then
        echo "📂 Cambios locales detectados, subiendo a GitHub..."
        git add .
        git commit -m "Auto-sync: cambios detectados en $(date '+%Y-%m-%d %H:%M:%S')"
        git push origin $BRANCH
        echo "✅ Cambios sincronizados a las $(date '+%H:%M:%S')"
    else
        echo "⏳ Sin cambios locales, solo sincronización remota."
    fi

    # Espera 30 segundos antes de revisar otra vez
    sleep 30
done