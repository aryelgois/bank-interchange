# Bank Interchange

Index:

- [Intro]
  - [pt_BR] | [en_US]
- [Setup]
- [TODO]
- [Documentation]
- [Changelog]


## Intro

#### pt_BR

Esse pacote implementa
as especificações do CNAB240 e do CNAB400,
definido pelo FEBRABAN,
e contém geradores de boleto
para diversos bancos,
em PHP.

> O CNAB permite a comunicação
> entre empresas e bancos,
> organizando as informações em arquivos de texto
> com uma estrutura predefinida

O objetivo desse pacote é
automatizar a criação de Boletos bancários
e Arquivos Remessa,
e facilitar a leitura de Arquivos Retorno
em um servidor web:

1. Quando o cliente realiza uma compra,
   por exemplo,
   um Título bancário é criado

   - Esse Título pode ser representado
     como um boleto,
     em PDF

2. Um Arquivo Remessa,
   contendo um ou mais Títulos,
   é gerado e enviado ao banco
   antes que o cliente efetue o pagamento

3. O banco envia um Arquivo Retorno
   informando se o Título foi
   aceito,
   pago,
   tem algum erro,
   ou alguma outra ocorrência

4. Após o administrador conferir o resultado,
   o banco de dados é atualizado
   com novos dados


#### en_US

This package implements
CNAB240 and CNAB400 specifications,
defined by FEBRABAN (a Brazilian organization),
and contains bank billet generators
for various banks,
in PHP.

> The CNAB allows a comunication
> between enterprises and banks,
> organizing the information in text files
> with a predefined layout

This package aims to
automate the generation of bank billets
and Shipping Files,
and to help reading Return Files
in a web server:

1. When your client buys something,
   for exemple,
   a banking Title is created

   - This Title can be rendered
     as a bank billet,
     in PDF

2. A Shipping File,
   containing one or more Titles,
   is generated and sent to the bank
   before the client makes the payment

3. The bank sends a Return File
   informing if the Title was
   accepted,
   paid,
   has an error,
   or some other occurrence

4. After the administrator checks the result,
   the database is updated
   with new data


## Setup

1. Clone with Git

2. Create a web server with PHP 7 or higher

   - Using Apache is recommended
     because the `.htaccess` files are already created

     - Enable the `AllowOverride` directive

   - Set the Document Root to `public/`

   - If you are using HTTPS,
     uncomment the line with `SSLRequireSSL` directive
     in `public/.htaccess`.
     It is highly recommended that you use SSL

3. Build the databases
   and run the generated SQL:
   _see [YASQL-PHP][aryelgois/yasql-php]_

 ```bash
composer yasql-build && cat build/*.sql | mysql -u root -p
 ```

4. Change the database credentials in
   `config/medools.php`
   _see [Medools][aryelgois/Medools]_

5. Configure the Authentication secret in
   `config/router.yml`
   _see [Medools Router][aryelgois/medools-router]_

   - You also need to register users
     in the `authentications` table
     and add their `authorizations`

6. Now you can develop inside `public/`

   - Make your app ajax request from server's `/api/`


## TODO

- [ ] Real world tests
- [ ] A nice web interface


## [Documentation]


## [Changelog]


[Intro]: #intro
[pt_BR]: #pt_br
[en_US]: #en_us
[Setup]: #setup
[TODO]: #todo

[Documentation]: doc/README.md
[Changelog]: CHANGELOG.md

[aryelgois/medools]: https://github.com/aryelgois/Medools
[aryelgois/medools-router]: https://github.com/aryelgois/medools-router
[aryelgois/yasql-php]: https://github.com/aryelgois/yasql-php
