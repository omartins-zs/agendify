#!/usr/bin/env bash
set -e

# Aguarda o banco de dados estar disponível
echo "Aguardando o banco de dados iniciar..."
while ! nc -z postgres 5432; do
  sleep 0.5
done
echo "Banco de dados disponível!"

# Otimizações seguras de inicialização
# Não usaremos config:cache por padrão no local para não travar o desenvolvimento,
# a não ser que APP_ENV=production, mas limparemos os caches por segurança
echo "Limpando e aquecendo caches essenciais..."

if [ "$APP_ENV" != "local" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    # No ambiente local, limpa config para não ficar preso em configuração antiga
    php artisan config:clear
    php artisan route:clear
    # Mantém view cache ativado pois blade compiler é custoso, e ele se revalida sozinho no dev
    php artisan view:cache
fi

# Aguarda permissões mínimas no storage (útil após composer install no host)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "Iniciando PHP-FPM..."
exec php-fpm
