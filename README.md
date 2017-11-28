# Intro

## (pt_BR)

Esse pacote implementa as especificações do CNAB240 e do CNAB400, definido pelo
FEBRABAN, e geradores de boletos para diversos bancos, em PHP.

O CNAB permite a comunicação entre empresas e bancos, organizando as informações
em arquivos de texto com uma estrutura predefinida.

O objetivo desse pacote é automatizar a criação de Boletos bancários e Arquivos
Remessa, e a leitura de Arquivos Retorno em um servidor web:

- O Boleto seria gerado quando o cliente realizasse uma compra, por exemplo.
- O Arquivo Remessa seria gerado logo em seguida, devendo ser enviado ao banco
  antes que o cliente efetue o pagamento.
- O Arquivo Retorno enviado pelo banco informa se o pagamento foi efetuado, além
  de outros detalhes, e ativaria alguns processos automáticos no servidor.


## (en_US)

This package implements CNAB240 and CNAB400 specifications, defined by FEBRABAN
(a Brazilian organization), and bank billet generator for various banks, in PHP.

The CNAB allows a comunication between enterprises and banks, organizing the
information in text files with a predefined layout.

This package aims to automatize the generation of Bank billets and Shipping
Files, and the reading of Return Files in a webserver:

- The billet would be generated when your client buys something, for example.
- The Shipping File would be generated soon after, and should be sent to the
  bank before the client makes the payment.
  billets of that day.
- The Return File sent by the bank tells if the payment was accomplished,
  besides other details, and would trigger some hooks in the server.


# Example

There is a well designed example you can explore! It shows a simple way to
implement the package in a website.

You can insert data in the Database, generate bank billets and shipping files.
These shipping files can be viewed in both CNAB240 and CNAB400. Also, there is
a simple Return File analyzer.


# TODO

The script kinda works.. It's under development.

- [ ] Code review
- [ ] Real world test CNAB240 and CNAB400.
- [x] Write the Return File interpreter for CNAB240 and CNAB400.
  - [x] Make it interact with the Database
  - [ ] It should receive the data somehow.. fetch from the bank's site or
    provide a user input?
- [ ] Create hooks for Return Files.
  - [ ] A nice interface to integrate with one's website.
