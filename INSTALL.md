# Guia de instalação passo a passo do Prodmais UNIFESP

## Linux

### Instalação do Elasticsearch

O primeiro passo é instalar o Elasticsearch:

    sudo apt-get install apt-transport-https
    wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo gpg --dearmor -o /usr/share/keyrings/elasticsearch-keyring.gpg
    echo "deb [signed-by=/usr/share/keyrings/elasticsearch-keyring.gpg] https://artifacts.elastic.co/packages/8.x/apt stable main" | sudo tee /etc/apt/sources.list.d/elastic-8.x.list
    sudo apt-get update && sudo apt-get install elasticsearch

Na versão 8 do Elasticsearch, ele vem com a segurança habilitada por padrão. É preciso anotar a senha gerada na instalação.

Em ambientes de testes, é possível desabilitar a senha:

    nano /etc/elasticsearch/elasticsearch.yml

E alterar para false: xpack.security.enabled: false

Em ambientes de produção, é necessário utizar a senha gerada na instalação e alterar o arquivo inc/functions.php

    nano inc/functions.php

Na linha 17, alterar de:

    $client = ClientBuilder::create()
        ->setHosts(['localhost:9200'])
        ->build();

Para:

    $client = ClientBuilder::create()
        ->setHosts(['https://localhost:9200'])
        ->setBasicAuthentication('elastic','SENHA')
        ->setCABundle('/var/www/prodmais/inc/http_ca.crt')
        ->build();

Caso queira testar se o Elasticseach está funcionando

    export ELASTIC_PASSWORD="SENHA"
    curl --cacert /etc/elasticsearch/certs/http_ca.crt -u elastic:$ELASTIC_PASSWORD https://localhost:9200

Comandos para iniciar o Elasticsearch:

    sudo systemctl start elasticsearch.service
    sudo systemctl stop elasticsearch.service

Comandos para iniciar o Elasticsearch ao inicializar o sistema:

    sudo /bin/systemctl daemon-reload
    sudo /bin/systemctl enable elasticsearch.service

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

        <Directory /var/www/html>
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>

### Clonagem do repositório do Prodmais

Você pode clonar em qualquer pasta, mas é recomendável clonar na pasta pública do apache (ex. /var/www):

    git clone https://github.com/unifesp/prodmais.git html

Na pasta do repositório, rodar:

    curl -s https://getcomposer.org/installer | php
    php composer.phar install --no-dev

Copiar o arquivo config_example.php para o arquivo config.php

    cp inc/config_example.php inc/config.php

Editar o arquivo config.php

    nano inc/config.php

Editar no arquivo config.php as variáveis: $branch, $branch_description, $url_base, $facebook_image (opcional) e $instituicao.

Após editar o arquivo config.php, rodar ele pela primeira vez num browser, usando o endereço http://localhost/NOMEDODIRETÓRIO

Ao rodar pela primeira vez, o sistema irá criar os índices no elasticsearch.

Criar o diretório tmp

    mkdir tmp
    chown -R www-data:www-data tmp
    mkdir data
    chown -R www-data:www-data data

### Google Analytics

Criar o arquivo inc/google_analytics.php

    touch inc/google_analytics.php

Copie o código do Google Analytics no arquivo inc/google_analytics.php

### Inclusão automática

Enviar dados por POST no arquivo import_lattes_to_elastic_dedup.php. Parâmetros aceitos:

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
