<?php

function temPapel($papeis_permitidos)
{
    return in_array($_SESSION['usuario_papel'], $papeis_permitidos);
}