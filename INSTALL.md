# Guia de instalação passo a passo do Prodmais UNIFESP

## Linux

### Instalação do Elasticsearch

O primeiro passo é instalar o Elasticsearch:

    sudo apt-get install apt-transport-https
    wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo gpg --dearmor -o /usr/share/keyrings/elasticsearch-keyring.gpg
    echo "deb [signed-by=/usr/share/keyrings/elasticsearch-keyring.gpg] https://artifacts.elastic.co/packages/8.x/apt stable main" | sudo tee /etc/apt/sources.list.d/elastic-8.x.list
    sudo apt-get update && sudo apt-get install elasticsearch

Por padrão, o elasticseach não exige senha na instalação.

### Instalação do PHP 8.2

    sudo apt -y install lsb-release apt-transport-https ca-certificates
    sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
    sudo apt update
    sudo apt -y install php8.2
    sudo apt-get install php8.2-{cgi,curl,mbstring,zip,xml}

### Instalação do Apache2

    sudo apt update
    sudo apt install apache2

#### Habilitar o mod_rewrite

    sudo a2enmod rewrite

    E adicionar ao apache conf:

        <Directory /var/www/html/prodmais>
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>

### Clonagem do repositório do Prodmais

Você pode clonar em qualquer pasta, mas é recomendável clonar na pasta pública do apache (ex. /var/www/html):

    git clone https://github.com/unifesp/prodmais.git

Na pasta do repositório, rodar:

    curl -s https://getcomposer.org/installer | php
    php composer.phar install --no-dev

Copiar o arquivo config_example.php para o arquivo config.php

    cp inc/config_example.php inc/config.php

Editar o arquivo config.php

    nano inc/config.php

Criar o diretório tmp

    mkdir tmp
    chown -R www-data:www-data tmp

Editar no arquivo config.php as variáveis: $branch, $branch_description, $url_base, $facebook_image (opcional) e $instituicao.

Após editar o arquivo config.php, rodar ele pela primeira vez num browser, usando o endereço htttp://localhost/NOMEDODIRETÓRIO

Ao rodar pela primeira vez, o sistema irá criar os índices no elasticsearch.

### Google Analytics

Criar o arquivo inc/google_analytics.php

    touch inc/google_analytics.php

Copie o código do Google Analytics no arquivo inc/google_analytics.php

### Inclusão automática

Parâmetros aceitos no import_lattes_to_elastic_dedup.php

    tag
    unidade
    departamento
    tipvin
    numfuncional
    divisao
    secao
    ppg_nome
    ppg_capes
    genero
    etnia
    desc_nivel
    desc_curso
    ano_ingresso
    campus
    desc_gestora

Exemplo de código para a inclusão automática: tools/automatic_index.php

## Usando Docker

[Baixe estes arquivos](https://github.com/RicardoIreno/prodmais-docker)
