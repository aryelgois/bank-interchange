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

There is an example simulating an e-Comerce. You can choose between CNAB240 and
CNAB400, and there are just minor differences between the required data.


# TODO

I know, the script is not working yet.. It's under development.

- [x] Write the Shipping File generator for CNAB400.
- [ ] Real world test CNAB240 and CNAB400.
- [ ] Write the Return File interpreter for CNAB240 and CNAB400.
  - [ ] It should receive the data somehow.. fetch from the bank's site or
    provide as local files?
- [ ] Create hooks for Return Files.
  - [ ] A nice interface to integrate with one's website.
- [ ] Configure packagist.
