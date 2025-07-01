<?php

namespace Laraveltoolkit\Tests\Model;

class UserPermission extends \Laraveltoolkit\ACL\UserPermission
{
    /**
     * {@inheritDoc}
     */
    protected function declarePoliciesAndRoles(): void
    {
        self::registryPolicy('users', 'Usuários', 'Gerencia os usuários do sistema')
            ->crud()->rule('abc', 'foo', 'dsa');
        self::registryPolicy('products', 'Produtos', 'Gerencia os produtos do sistema')
            ->crud();
        self::registryPolicy('categories', 'Categorias', 'Gerencia os categorias do sistema')
            ->crud();
        //        // OR
        //        self::registryPolicy('users', 'Usuários', 'Gerencia os usuários do sistema')
        //            ->rule('create', 'Criar', 'Cria um usuário no sistema')
        //            ->rule('read', 'Ler', 'Ler um usuário no sistema')
        //            ->rule('update', 'Atualizar', 'Atualizar um usuário no sistema')
        //            ->rule('delete', 'Deletar', 'Deletar um usuário no sistema');
    }
}
