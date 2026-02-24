<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas;

use Amicus\FilamentEmployeeManagement\Filament\Pages\MissingEmployeePage;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        $isCurrentRouteMissingEmployeePage = request()->routeIs(MissingEmployeePage::getRouteName());

        return $schema
            ->columns(2)
            ->components([
                Section::make('Osobni podaci')
                    ->description('Osnovne informacije o zaposleniku')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('first_name')
                                ->label('Ime')
                                ->prefixIcon(Heroicon::OutlinedUser)
                                ->placeholder('Ivan')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('last_name')
                                ->label('Prezime')
                                ->prefixIcon(Heroicon::OutlinedUser)
                                ->placeholder('Horvat')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('oib')
                                ->label('OIB')
                                ->prefixIcon(Heroicon::OutlinedIdentification)
                                ->required()
                                ->placeholder('12345678901')
                                ->numeric()
                                ->helperText('Osobni identifikacijski broj, 11 znamenki.'),

                            Forms\Components\TextInput::make('address')
                                ->required()
                                ->label('Adresa')
                                ->prefixIcon(Heroicon::OutlinedMapPin)
                                ->placeholder('Ilica 1')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('city')
                                ->required()
                                ->label('Grad')
                                ->prefixIcon(Heroicon::OutlinedBuildingOffice2)
                                ->placeholder('Zagreb')
                                ->maxLength(255),
                        ]),
                    ]),

                Section::make('Kontakt podaci')
                    ->description('Email adresa i telefonski brojevi')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->prefixIcon(Heroicon::OutlinedEnvelope)
                            ->placeholder('ivan.horvat@primjer.com')
                            ->email()
                            ->required()
                            ->unique('employees', 'email', ignoreRecord: true)
                            ->rules([
                                fn ($record) => \Illuminate\Validation\Rule::unique('users', 'email')
                                    ->ignore($record?->user_id),
                            ])
                            ->validationMessages([
                                'unique' => 'Email adresa je već u upotrebi.',
                            ])
                            ->maxLength(255),

                        Forms\Components\Repeater::make('phone_numbers')
                            ->label('Brojevi telefona')
                            ->collapsible()
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('number')
                                    ->label('Broj')
                                    ->required()
                                    ->placeholder('+385 91 123 4567'),
                                Forms\Components\Radio::make('type')
                                    ->label('Tip')
                                    ->options(\Amicus\FilamentEmployeeManagement\Enums\PhoneNumberType::class)
                                    ->inline()
                                    ->default(\Amicus\FilamentEmployeeManagement\Enums\PhoneNumberType::PRIVATE)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Zaposlenje')
                    ->description('Odjel i pristupni podaci')
                    ->schema([
                        Forms\Components\Select::make('department_id')
                            ->label('Odjel')
                            ->relationship('department', 'name')
                            ->helperText('Odaberite odjel kojem zaposlenik pripada.')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Naziv odjela')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('job_position')
                            ->label('Radno mjesto')
                            ->prefixIcon(Heroicon::OutlinedBriefcase)
                            ->placeholder('npr. Voditelj projekta')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Lozinka')
                            ->prefixIcon(Heroicon::OutlinedLockClosed)
                            ->password()
                            ->placeholder('*********')
                            ->required(fn (string $context, $get): bool => $context === 'create' && empty($get('user_id')))
                            ->visible(fn ($get): bool => empty($get('user_id')))
                            ->revealable()
                            ->helperText('Lozinka je obavezna kad nije odabran postojeći korisnik. Lozinka mora sadržavati najmanje 8 znakova.'),
                    ]),

                Section::make('Administracija')
                    ->description('Napomene, potpis i postavke korisničkog računa')
                    ->columnSpanFull()
                    ->visible(fn () => ! $isCurrentRouteMissingEmployeePage && auth()->user()->isAdmin())
                    ->schema([
                        Forms\Components\Textarea::make('note')
                            ->label('Napomena')
                            ->placeholder('Dodatne napomene o zaposleniku.'),

                        Grid::make(2)->schema([
                            FileUpload::make('signature_path')
                                ->label('Potpis')
                                ->helperText('Potpis zaposlenika koji će se koristiti na HR dokumentima (npr. zahtjevima za godišnji odmor).')
                                ->image()
                                ->disk('public')
                                ->previewable()
                                ->downloadable()
                                ->directory('hr-documents/signatures')
                                ->visibility('public'),

                            Forms\Components\CheckboxList::make('role')
                                ->label('Uloga')
                                ->options(DB::table('roles')->pluck('name', 'id')->map(fn ($record) => ucwords(str_replace('_', ' ', $record))))
                                ->formatStateUsing(function ($record) {
                                    return $record?->user?->roles?->pluck('id')->toArray() ?? [];
                                })
                                ->required()
                                ->helperText('Odaberite ulogu za novog zaposlenika.'),
                        ]),

                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->label('Je li zaposlenik aktivan korisnik sustava?')
                            ->helperText('Ako je zaposlenik neaktivan, neće moći pristupiti sustavu, neće se prikazivati u popisu zaposlenika.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function monthlyTimeReport(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('month')
                ->label('Mjesec')
                ->options([
                    1 => '1. Siječanj',
                    2 => '2. Veljača',
                    3 => '3. Ožujak',
                    4 => '4. Travanj',
                    5 => '5. Svibanj',
                    6 => '6. Lipanj',
                    7 => '7. Srpanj',
                    8 => '8. Kolovoz',
                    9 => '9. Rujan',
                    10 => '10. Listopad',
                    11 => '11. Studeni',
                    12 => '12. Prosinac',
                ])
                ->searchable()
                ->selectablePlaceholder(false)
                ->preload()
                ->default(now()->month)
                ->required(),

            Select::make('year')
                ->label('Godina')
                ->options(collect(range(now()->year - 2, now()->year + 1))->mapWithKeys(fn ($year) => [$year => $year]))
                ->default(now()->year)
                ->searchable()
                ->selectablePlaceholder(false)
                ->preload()
                ->required(),
        ]);

    }

    public static function getTelegramChatIdField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('telegram_chat_id')
            ->label('Telegram Chat ID')
            ->required()
            ->placeholder('Unesite Telegram Chat ID')
            ->helperText('Ovaj ID se koristi za slanje obavijesti putem Telegrama.')
            ->maxLength(255)
            ->columnSpanFull();
    }
}
