<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Email</title>
<?php $this->head() ?>

<style type="text/css">
    /* Prevent Windows 10 Mail from underlining links. Styles for underlined links should be inline. */
    a {
        text-decoration: none;
    }

    @media all and (min-width: 360px) {
        .email-button a {
            width: 250px !important;
        }
    }

    /* Media queries (works only for gmail) */
    @media all and (min-width: 500px) {
        body {
            padding-top: 15px !important;
            padding-bottom: 15px !important;
        }

        .body {
            padding-left: 70px !important;
            padding-right: 70px !important;
        }

        .table-img {
            display: table-cell !important;
            width: 115px;
            text-align: left !important;
        }

        .table-description {
            text-align: left !important;
        }

        .pt-20-desktop {
            padding-top: 20px !important;
        }

        .email-button {
            padding-top: 25px !important;
            padding-bottom: 25px !important;
        }

        .logo {
            padding-bottom: 35px !important;
        }
    }
</style>
