# 1) carpetas
mkdir -p ai/providers deploy

# 2) copia todos los archivos de arriba en su ruta

# 3) Python venv + deps
cd ai
python3 -m venv .venv
.venv/bin/pip install -q -r requirements.txt
cp .env.example .env
nano .env       # pega OPENROUTER_API_KEY, GEMINI_API_KEY, GROQ_API_KEY
cd ..

# 4) permisos (Apache ejecuta el venv como www-data)
sudo chown -R www-data:www-data ai/
sudo chmod -R u+rwX,go+rX ai/
sudo chgrp www-data ai/.env
sudo chmod 640 ai/.env

# 5) Apache
sudo cp deploy/apache-makka-ai.conf /etc/apache2/conf-available/makka-ai.conf
sudo a2enmod proxy proxy_http
sudo a2enconf makka-ai
sudo systemctl reload apache2

# 6) Ollama con el modelo
ollama pull maternion/mimo-v2.6:9b
