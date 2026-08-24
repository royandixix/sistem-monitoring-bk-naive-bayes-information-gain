<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Siswa;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                /*
                |--------------------------------------------------------------------------
                | Data akun
                |--------------------------------------------------------------------------
                */
                Section::make('Data Akun')
                    ->description(
                        'Kelola data login, email, password, dan role pengguna.'
                    )
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextInput::make('nip')
                            ->label('NIP / ID Pengguna')
                            ->placeholder('Contoh: WM002')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->autocomplete(false),

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Masukkan nama lengkap')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete(false),

                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Contoh: orangtua@gmail.com')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->autocomplete(false),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(
                                fn (string $operation): bool =>
                                    $operation === 'create'
                            )
                            ->dehydrated(
                                fn (?string $state): bool =>
                                    filled($state)
                            )
                            ->helperText(
                                'Kosongkan password ketika mengedit jika tidak ingin mengubahnya.'
                            )
                            ->maxLength(255),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Diverifikasi Pada')
                            ->seconds(false)
                            ->default(now())
                            ->native(false),

                        Select::make('role')
                            ->label('Role')
                            ->options([
                                'super_admin' => 'Guru BK',
                                'admin' => 'OSIS',
                                'kepala_sekolah' => 'Kepala Sekolah',
                                'wali_murid' => 'Wali Murid',
                            ])
                            ->default('wali_murid')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(
                                function (
                                    ?string $state,
                                    Set $set
                                ): void {
                                    /*
                                     * Wali murid tidak menggunakan kelas_id,
                                     * karena satu wali dapat mempunyai anak
                                     * dari beberapa kelas.
                                     */
                                    if ($state === 'wali_murid') {
                                        $set('kelas_id', null);

                                        return;
                                    }

                                    /*
                                     * Kosongkan pilihan anak ketika role
                                     * bukan Wali Murid.
                                     */
                                    $set('anak', []);
                                }
                            ),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Hubungan wali murid dengan anak
                |--------------------------------------------------------------------------
                */
                Section::make('Data Anak')
                    ->description(
                        'Hubungkan satu akun Wali Murid dengan satu atau beberapa siswa.'
                    )
                    ->icon('heroicon-o-user-group')
                    ->visible(
                        fn (Get $get): bool =>
                            $get->string(
                                'role',
                                isNullable: true
                            ) === 'wali_murid'
                    )
                    ->schema([
                        Select::make('anak')
                            ->label('Anak yang Terhubung')
                            ->multiple()
                            ->relationship(
                                name: 'anak',
                                titleAttribute: 'nama',
                                modifyQueryUsing:
                                    fn (Builder $query): Builder =>
                                        $query
                                            ->with('kelas')
                                            ->orderBy('nis')
                            )
                            ->getOptionLabelFromRecordUsing(
                                function (Siswa $record): string {
                                    $kelas =
                                        $record
                                            ->kelas
                                            ?->nama_kelas
                                        ?? 'Tanpa Kelas';

                                    return
                                        "{$record->nis} - " .
                                        "{$record->nama} - " .
                                        "{$kelas}";
                                }
                            )
                            ->searchable([
                                'nis',
                                'nama',
                            ])
                            ->preload()
                            ->native(false)
                            ->required(
                                fn (Get $get): bool =>
                                    $get->string(
                                        'role',
                                        isNullable: true
                                    ) === 'wali_murid'
                            )
                            ->pivotData([
                                'hubungan' => 'Orang Tua/Wali',
                                'is_primary' => true,
                            ])
                            ->helperText(
                                'Pilih satu atau beberapa anak. Anak dapat berada pada kelas yang sama maupun kelas yang berbeda.'
                            )
                            ->searchPrompt(
                                'Cari berdasarkan NIS atau nama siswa'
                            )
                            ->noSearchResultsMessage(
                                'Siswa tidak ditemukan.'
                            )
                            ->noOptionsMessage(
                                'Belum ada data siswa.'
                            )
                            ->columnSpanFull(),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Data profil
                |--------------------------------------------------------------------------
                */
                Section::make('Data Profil')
                    ->description(
                        'Lengkapi informasi kontak dan profil pengguna.'
                    )
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextInput::make('phone')
                            ->label('Nomor HP')
                            ->placeholder('Contoh: 081234567890')
                            ->tel()
                            ->maxLength(30)
                            ->autocomplete(false),

                        /*
                         * Kelas tidak ditampilkan untuk Wali Murid.
                         * Wali Murid menggunakan relasi anak, bukan kelas_id.
                         */
                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->relationship(
                                name: 'kelas',
                                titleAttribute: 'nama_kelas',
                                modifyQueryUsing:
                                    fn (Builder $query): Builder =>
                                        $query
                                            ->orderBy(
                                                'tahun_ajaran',
                                                'desc'
                                            )
                                            ->orderBy('nama_kelas')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn ($record): string =>
                                    "{$record->nama_kelas} - " .
                                    "{$record->tahun_ajaran}"
                            )
                            ->searchable([
                                'kode_kelas',
                                'nama_kelas',
                            ])
                            ->preload()
                            ->native(false)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get->string(
                                        'role',
                                        isNullable: true
                                    ) !== 'wali_murid'
                            ),

                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->placeholder('Pilih jenis kelamin')
                            ->native(false),

                        TextInput::make('alamat')
                            ->label('Alamat')
                            ->placeholder('Masukkan alamat pengguna')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}