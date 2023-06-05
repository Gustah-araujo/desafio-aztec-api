# Desafio Aztec API

Desafio de desenvolvimento de API realizado para o processo seletivo da Aztec Soluções Digitais

### Atenção
A **.env.example** deste projeto foi feita tendo em mente o uso do laradock que será descrito abaixo. Caso deseje executar esse projeto sem a utilização do laradock, terá que atualizar a sua **.env**.
## Instalação

Clone o repositório

`https://github.com/Gustah-araujo/desafio-aztec-api.git`

Dentro da pasta do projeto, renomeie o arquivo **.env.example** para **.env**

`cp .env.example .env`

Importe o laradock

`git clone https://github.com/laradock/laradock.git`

Renomeie a **.env.example** dentro da pasta /laradock para **.env**

`cd laradock`

`cp .env.example .env`

Execute os contêiners necessários para a execução da aplicação

`docker compose up -d nginx workspace mysql`
ou
`docker-compose up -d nginx workspace mysql`

Acesse o container da aplicação

`docker compose exec workspace bash`
ou
`docker-compose exec workspace bash`

Execute todos os comandos necessários para o setup inicial da aplicação

`php artisan key:generate`

`composer install`

`npm install`

`npm run dev`

Pronto, a aplicação está pronta para ser utilizada.

## Utilização

Após instalação, a documentação da API pode ser acessada na [página raíz](http://localhost) da aplicação em seu navegador

Dentro deste repositório estão disponíveis um arquivo JSON com a [coleção Postman](/workspace.postman_globals.json) e um arquivo com as [variáveis globais](/workspace.postman_globals.json) usadas pela aplicação no Postman.

### Autenticação

Para executar qualquer rota é obrigatório incluir nas Headers da requisição o Bearer Token que pode ser gerado pela própria autenticação (**Não se preocupe**, a coleção do Postman atualiza a variável com o token automaticamente toda vez que disparar uma nova requisição para a rota de gerar Token)
