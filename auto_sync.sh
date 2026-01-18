#!/bin/bash

# Ruta de tu proyecto
PROJECT_DIR="/c/Users/Josue/Documents/becas"
BRANCH="main"

cd "$PROJECT_DIR" || exit

echo "🔄 Auto Sync iniciado en $PROJECT_DIR (rama $BRANCH)..."

while true; do
    # Verifica si hay cambios sin guardar
    if ! git diff --quiet || ! git diff --cached --quiet; then
        echo "📂 Cambios detectados, sincronizando con GitHub..."

        git add .
        git commit -m "Auto-sync: cambios detectados en $(date '+%Y-%m-%d %H:%M:%S')"
        git pull origin $BRANCH --allow-unrelated-histories --no-edit
        git push origin $BRANCH

        echo "✅ Cambios sincronizados a las $(date '+%H:%M:%S')"
    else
        echo "⏳ Sin cambios, esperando..."
    fi

    # Espera 60 segundos antes de revisar otra vez
    sleep 1
done
