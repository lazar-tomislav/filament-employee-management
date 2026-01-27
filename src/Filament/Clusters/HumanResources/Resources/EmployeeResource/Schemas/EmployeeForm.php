<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas;

use Amicus\FilamentEmployeeManagement\Filament\Pages\MissingEmployeePage;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        // if auth user is not employee then show this field
        $isUserEmployee = ! auth()->user()->isEmployee();
        $isCurrentRouteMissingEmployeePage = request()->routeIs(MissingEmployeePage::getRouteName());

        return $schema
            ->columns(2)
            ->components([
                Forms\Components\TextInput::make('first_name')
                    ->label('Ime')
                    ->placeholder('Ivan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('last_name')
                    ->label('Prezime')
                    ->placeholder('Horvat')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->placeholder('ivan.horvat@primjer.com')
                    ->email()
                    ->required()
                    ->unique('employees', 'email', ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Repeater::make('phone_numbers')
                    ->label('Brojevi telefona')
                    ->collapsed()
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
                    ])
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('password')
                    ->label('Lozinka')
                    ->password()
                    ->placeholder('*********')
                    ->columnSpanFull()
                    ->required(fn (string $context, $get): bool => $context === 'create' && empty($get('user_id')))
                    ->visible(fn ($get): bool => empty($get('user_id')))
                    ->helperText('Lozinka je obavezna kad nije odabran postojeći korisnik. Lozinka mora sadržavati najmanje 8 znakova.'),

                Forms\Components\TextInput::make('oib')
                    ->label('OIB')
                    ->required()
                    ->placeholder('12345678901')
                    ->numeric()
                    ->helperText('Osobni identifikacijski broj, 11 znamenki.'),

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

                Forms\Components\TextInput::make('address')
                    ->required()
                    ->label('Adresa')
                    ->placeholder('Ilica 1')
                    ->maxLength(255),

                Forms\Components\TextInput::make('city')
                    ->required()
                    ->label('Grad')
                    ->placeholder('Zagreb')
                    ->maxLength(255),

                Forms\Components\Textarea::make('note')
                    ->label('Napomena')
                    ->visible(fn () => ! $isCurrentRouteMissingEmployeePage && ! $isUserEmployee)
                    ->placeholder('Dodatne napomene o zaposleniku.')
                    ->columnSpanFull(),

                Forms\Components\CheckboxList::make('role')
                    ->label('Uloga')
                    ->options(DB::table('roles')->pluck('name', 'id')->map(fn ($record) => ucwords(str_replace('_', ' ', $record))))
                    ->formatStateUsing(function ($record) {
                        return $record?->user?->roles?->pluck('id')->toArray() ?? [];
                    })
                    ->required()
                    ->visible(fn () => auth()->user()->isAdmin() && ! $isCurrentRouteMissingEmployeePage)
                    ->helperText('Odaberite ulogu za novog zaposlenika.'),

                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->columnSpanFull()
                    ->label('Je li zaposlenik aktivan korisnik sustava?')
                    ->helperText('Ako je zaposlenik neaktivan, neće moći pristupiti sustavu, neće se prikazivati u popisu zaposlenika.')
                    ->visible(fn () => ! $isCurrentRouteMissingEmployeePage && ! $isUserEmployee)
                    ->default(true),
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
