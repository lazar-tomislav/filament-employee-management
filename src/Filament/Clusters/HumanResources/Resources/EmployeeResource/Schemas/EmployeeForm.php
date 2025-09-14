<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas;

use Amicus\FilamentEmployeeManagement\Filament\Pages\MissingEmployeePage;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        // if auth user is not employee then show this field
        $isUserEmployee = !auth()->user()->isEmployee();
        $isCurrentRouteMissingEmployeePage = request()->routeIs(MissingEmployeePage::getRouteName());

        return $schema
            ->columns(2)
            ->extraAttributes(['class' => 'max-w-2xl mx-auto'])
            ->components([
                Section::make('Predispuni po postojećem korisniku')
                    ->columnSpan(1)
                    ->columnSpan(2)
                    ->visibleOn(Operation::Create)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label("Postojeći korisnik")
                            ->options(function () {
                                // all users which do not have an employee record
                                return User::query()
                                    ->whereDoesntHave('employee')
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->live()
                            ->visibleOn(Operation::Create)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if($state === null){
                                    return;
                                }
                                $user = User::find($state);
                                if($user){
                                    $set('first_name', $user->name);
                                    $set('email', $user->email);
                                }
                            })
                            ->columnSpan(1)
                            ->helperText('Kad već imamo korisnika u sustavu, možemo ga povezati s zaposlenikom, na odabir će se automatski predispuniti dostupni podaci.')
                            ->preload(),
                    ]),

                Section::make('Osobni i kontakt podaci')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
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

                        Forms\Components\TextInput::make('phone_number')
                            ->label('Broj telefona')
                            ->required()
                            ->placeholder('+385 91 123 4567')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Lozinka')
                            ->password()
                            ->placeholder('*********')
                            ->columnSpanFull()
                            ->required(fn(string $context, $get): bool => $context === 'create' && empty($get('user_id')))
                            ->visible(fn($get): bool => empty($get('user_id')))
                            ->helperText('Lozinka je obavezna kad nije odabran postojeći korisnik. Lozinka mora sadržavati najmanje 8 znakova.'),

                        Forms\Components\TextInput::make('oib')
                            ->label('OIB')
                            ->required()
                            ->placeholder('12345678901')
                            ->numeric()
                            ->helperText('Osobni identifikacijski broj, 11 znamenki.'),

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

                        Forms\Components\TextInput::make('mobile_tariff')
                            ->label('Tarifa mobilnog broja')
                            ->placeholder('L Tarifa')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('note')
                            ->label('Napomena')
                            ->visible(fn() => !$isCurrentRouteMissingEmployeePage && !$isUserEmployee)
                            ->placeholder('Dodatne napomene o zaposleniku.')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->columnSpanFull()
                            ->label('Je li zaposlenik aktivan korisnik sustava?')
                            ->helperText('Ako je zaposlenik neaktivan, neće moći pristupiti sustavu, neće se prikazivati u popisu zaposlenika.')
                            ->visible(fn() => !$isCurrentRouteMissingEmployeePage && !$isUserEmployee)
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function monthlyTimeReport(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('month')
                ->label('Mjesec')
                ->options([
                    1 => 'Siječanj',
                    2 => 'Veljača',
                    3 => 'Ožujak',
                    4 => 'Travanj',
                    5 => 'Svibanj',
                    6 => 'Lipanj',
                    7 => 'Srpanj',
                    8 => 'Kolovoz',
                    9 => 'Rujan',
                    10 => 'Listopad',
                    11 => 'Studeni',
                    12 => 'Prosinac',
                ])
                ->default(now()->subMonth()->month)
                ->required(),
            Select::make('year')
                ->label('Godina')
                ->options(collect(range(now()->year - 2, now()->year + 1))->mapWithKeys(fn($year) => [$year => $year]))
                ->default(now()->year)
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
