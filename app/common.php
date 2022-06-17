<?php
// 应用公共文件

function createToken($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}
