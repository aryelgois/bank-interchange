# Intro

## (pt_BR)

Esse pacote implementa as especificações do CNAB240, definido pelo FEBRABAN, em
PHP.

O CNAB permite a comunicação entre empresas e bancos, organizando as informações
em arquivos de texto em que cada linha tem 240 caracteres.

O objetivo desse pacote é automatizar o processo de criação de Arquivos Remessa
e de leitura de Arquivos Retorno em um website:

- O Arquivo Remessa seria gerado quando seu cliente realizasse uma compra, por
  exemplo.
- O Arquivo Retorno enviado pelo banco informa se o pagamento foi efetuado, além
  de outros detalhes, e ativaria alguns processos automáticos no site.


## (en_US)

This package implements CNAB240 specifications, defined by FEBRABAN (a Brazilian
organization), in PHP.

The CNAB allows a comunication between enterprises and banks, organizing the
information in text files whose each line has 240 characters.

This package aims to automatize the generation of Shipping Files and the reading
of Return Files in a website:

- The Shipping File would be generated when your client buys something, for
  example.
- The Return File sent by the bank tells if the payment was accomplished,
  besides other details, and would trigger some hooks in the site.


# Example

There is an example simulating an e-Comerce. It's a good example, but badly
implemented.. I am still learning about MVC and the Twig template engine.

By the way, twig is under require-dev because it's only used in the example, it
is not used in the actual scripts for this package.


# TODO

I know, the script is not working yet.. It's under development.

- [ ] Finish Shipping Files class
  - [ ] `registerLotDetail()` requires some data about the product and the payer
    (the client). It also has differents segments which have the same lot index.
  - [x] Test if the others "low level" methods are working.
  - [ ] Make the "high level" methods work.
- [ ] Write the Return File interpreter.
  - [ ] It should receive the data somehow.. fetch from the bank's site or
    provide as local files?
- [ ] Create hooks for Return Files.
  - [ ] A nice interface to integrate with one's website.
- [ ] Merge a (personal) project which generates bank billets.
  - It's personal because i didn't send to Github, and I want it to be part of
    this package. I will push later.
- [ ] Configure packagist.
