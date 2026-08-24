<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use SensitiveParameter;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getRoleFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getRoleFormComponent(): Component
    {
        return Select::make('role')
            ->label('Masuk Sebagai')
            ->placeholder('Pilih role pengguna')
            ->options([
                'super_admin' => 'Guru BK',
                'admin' => 'OSIS',
                'kepala_sekolah' => 'Kepala Sekolah',
                'wali_murid' => 'Wali Murid',
            ])
            ->required()
            ->native(false);
    }

    protected function getCredentialsFromFormData(
        #[SensitiveParameter] array $data
    ): array {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ];
    }
}
