# Bank Interchange


# Intro

## (pt_BR)

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


## (en_US)

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


# Setup

1. Install this package with composer:

  `composer require aryelgois/bank-interchange`

2. Add the `yasql-build` script, as explained in [yasql-php] Setup

3. Build the [yasql][] [databases] and run the generated SQL in your server:

 ```bash
composer yasql-build -- vendor=aryelgois/bank-interchange
ls build
 ```


# TODO

The script kinda works.. It's under development.

- [x] Code review
- [ ] Real world test CNAB240 and CNAB400.
- [x] Write the Return File interpreter for CNAB240 and CNAB400.
  - [x] Make it interact with the Database
  - [x] It should receive the data somehow.. fetch from the bank's site or
    provide a user input?
- [ ] Create hooks for Return Files.
  - [ ] A nice interface to integrate with one's website.


# [Changelog]


[databases]: config/databases.yml
[Changelog]: CHANGELOG.md

[yasql]: https://github.com/aryelgois/yasql
[yasql-php]: https://github.com/aryelgois/yasql-php
