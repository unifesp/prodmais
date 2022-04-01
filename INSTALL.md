# Guia de instalação passo a passo do Prodmais UNIFESP

## Linux

### Instalação do Elasticsearch

O primeiro passo é instalar o Elasticsearch:

    wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
    sudo apt-get install apt-transport-https
    echo "deb https://artifacts.elastic.co/packages/7.x/apt stable main" | sudo tee /etc/apt/sources.list.d/elastic-7.x.list
    sudo apt-get update && sudo apt-get install elasticsearch

Por padrão, o elasticseach não exige senha na instalação.

### Instalação do PHP 7.4

    sudo apt -y install lsb-release apt-transport-https ca-certificates 
    sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
    sudo apt update
    sudo apt -y install php7.4
    sudo apt-get install php7.4-{cgi,curl,mbstring,zip}


### Instalação do Apache2 

    sudo apt update
    sudo apt install apache2

### Clonagem do repositório do Prodmais

Você pode clonar em qualquer pasta, mas é recomendável clonar na pasta pública do apache (ex. /var/www/html): 

    git clone https://github.com/unifesp/prodmais.git

Na pasta do repositório, rodar: 

    curl -s http://getcomposer.org/installer | php
    php composer.phar install --no-dev
    git submodule init
    git submodule update

Copiar o arquivo config_example.php para o arquivo config.php

    cp inc/config_example.php inc/config.php

Editar o arquivo config.php

    nano inc/config.php

Editar no arquivo config.php as variáveis: $branch, $branch_description, $url_base, $background_1, $facebook_image (opcional) e $instituicao.

Após editar o arquivo config.php, rodar ele pela primeira vez num browser, usando o endereço htttp://localhost/NOMEDODIRETÓRIO

Ao rodar pela primeira vez, o sistema irá criar os índices no elasticsearch.