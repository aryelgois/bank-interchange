# Documentation

Index:

- [API]
  - [titles]
  - [shipping_files]
  - [return_file]
  - [Authentication]
- [Server side]
  - [Document Root]
  - [Models]
    - [Database]
  - [Views]
    - [BankBillet]
    - [ShippingFile]
  - [Controllers]
    - [FilePack]
  - [ReturnFile]


## API

Requests made to `/api/`
are processed by [Medools Router][aryelgois/medools-router].
There are some public resources
(read-only)
that you can `GET`,
but to access all resources
you need to [authenticate][Authentication].
A list of available resources
can be found [here][router.yml].

There are a few resources
that deserve a deeper description:


#### titles

A Title represents a transaction
between a client and an assignor
where a payment must be performed.
It might be for a product or service.

It is the biggest resource
in this API,
and can be rendered as PDF.
When requesting multiple titles in this format,
they are grouped in a ZIP archive,
where each title is in its own PDF file.


#### shipping_files

It groups one or more titles
in a CNAB document (`.REM`)
that can be sent to banks.
Just like `titles`,
multiple resources are grouped
in a ZIP archive.

Additionally,
this resource supports
a special query parameter
`?with_billets`
that makes the shipping file's titles
to be included in the archive.


#### return_file

Although it is not an actual resource,
this API endpoint allows a `POST`
with a return file in the payload
to be parsed.
By default, it also extracts
the most useful fields,
processes the occurrence message,
and tries to determine
`assignments` and `titles` ids.

The client must send a `text/plain`,
while the server responds with either:

- `application/x.bank-interchange.return-file_extracted+json`

  It has a predictable structure:

 ```json
{
    "return_file": {
        "charging": {
            "keys with charging data specific by bank, usually
            'cX_*' so you need to check when they exist": 0
        },
        "emission": "0000-00-00",
        "sequence": 1
    },
    "titles": [
        {
            "assignment": "1",
            "id": "1",
            "due": "0000-00-00",
            "occurrence": "Usually a human readable message",
            "occurrence_date": "0000-00-00",
            "our_number": 1,
            "tax": 0,
            "value": 0,
            "value_received": 0
        }
    ]
}
 ```

  If the title's `assignment` or `id`
  could not be detected,
  or if you don't have the authorization,
  they will be `null`

- `application/x.bank-interchange.return-file_parsed+json`

  It is a way more nested JSON
  with a structure dependent on
  the CNAB layout and bank.
  It tells the bank code,
  CNAB and registries' type,
  though.

You can control
which representation will be sent
with an Accept header.

Return files don't have a model
because they may
not always relate to
only one shipping file,
and may even
have titles from different shipping files.
Also, it can be incomplete
or require manual changes
before applying in the database.

So, the solution:
the client sends the return file
they got from the bank,
this endpoint parses it
and returns useful fields,
the client reviews
and then may request the changes
to the appropriate resources.


### Authentication

If you are registered,
send your `Basic` credentials
to `/api/auth/`.
It responds with a JWT token
that you need to send
in future requests
with a `Bearer` Authorization header.
If this token has expirated,
you need to re-authenticate.

Your authentication token
may authorize access to
only some resources
(always including public ones),
as configured in the server side.


## Server side

### Document Root

`public/` should be the only directory
directly accessed by Apache
(or your web server of choice).

It comes with a skeleton
to you start developing.


### Models

Powered by [Medools][aryelgois/Medools],
the core models
are in the namespace `\aryelgois\BankInterchange\Models`

Basically:

- There are `banks` and `people`

  > it can be a physical person
  > or a juridic person,
  > i.e. any human or non-human entity

- Some people have `assignments` with banks,
  making them `assignors`

  > assignments are related to bank accounts

- Some other people are `clients` of
  those assignors

  > a person can be client of
  > multiple assignors

- Clients produce `titles` to
  one of their assignor's
  assignments

  > it could always be the same assignment
  > or any of their assignments

- Titles are grouped in `shipping_files`
  to be sent to banks

There are other models
that hold pieces of data,
to reduce repetition
and keep consistency.

If a Title needs to be modified,
it has to be duplicated
in the database,
to allow the previous shipping file
to show the old Title's state,
while the new shipping file
contains a different data.
The `assignment` and `our_number` columns
must be equal.


#### Database

It's schema
can be seen [here][bank_interchange.yml].
There are some dependency databases
whose schema are in [here][address.yml]
and [here][authentication.yml].

They are defined in [YASQL][aryelgois/yasql],
a specification to write SQL in YAML.

The core database
includes some programs
for data validation.


### Views

Titles have a `BankBillet` view
with the layout specific
to assignment's bank,
while shipping files have
a `ShippingFile` view
specific to both assignment's CNAB and
bank.

To support more banks
and CNAB layouts,
more views need to be added,
and they have to comply with
bank's specifications.

> The reading of these specifications
> is tiring and their access is not that easy.
> Also, personally I find CNAB layouts inefficient
> and too repetitive.


#### BankBillet

It supports
custom logo images
for banks and assignors,
located at
`assets/logos/<model>/<id>.*`.
The accepted formats are
`JPG`, `JPEG`, `PNG` and `GIF`.

Also, `config/billet.yml`
contains static data
included in all views.
Some fields support
a rudimentar template engine:
`header_info`,
`demonstrative`,
`instructions`.
It is based on `{{  }}` tags,
with context modifier (`$`)
and nested object (`->`)
support.


#### ShippingFile

It basically
runs multiple `sprintf`
with hard-coded formats.
The data is also normalized.

When using a custom
`movement`,
the registry fields are
masked out.


### Controllers

To connect the resource's models
with these views
inside Medools Router,
an external handler is used
to control the process.

It reads the resource request
and decides the specific view
to be used.


#### FilePack

This class provides the ability
to combine multiple models
in a ZIP archive.


### ReturnFile

This parser depends on
config files at
`config/return_file/cnab<cnab>/<bank_code>.yml`.
It determines which file to use
based on the overall line length
and some characters in the first line.

It can be best described as
a massive and recursive
try-and-error regex matcher.

The Extractor _knows_
which fields their related
return file has,
and its job is to group them
in a predictable structure.

> It is sad that
> some banks simply send
> important fields empty
> when there is an error in
> the shipping file.
> Or worst:
> they are always empty
> (they could help to determine the title id)


[API]: #api
[titles]: #titles
[shipping_files]: #shipping_files
[return_file]: #return_file
[Authentication]: #authentication
[Server side]: #server-side
[Document Root]: #document-root
[Models]: #models
[Database]: #database
[Views]: #views
[BankBillet]: #bankbillet
[ShippingFile]: #shippingfile
[Controllers]: #controllers
[FilePack]: #filepack
[ReturnFile]: #returnfile

[router.yml]: ../config/router.yml
[bank_interchange.yml]: ../data/bank_interchange.yml

[aryelgois/medools]: https://github.com/aryelgois/Medools
[aryelgois/medools-router]: https://github.com/aryelgois/medools-router
[aryelgois/yasql]: https://github.com/aryelgois/yasql

[address.yml]: https://github.com/aryelgois/databases/blob/master/data/address.yml
[authentication.yml]: https://github.com/aryelgois/medools-router/blob/master/data/authentication.yml
