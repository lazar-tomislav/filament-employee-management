<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Schemas;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->extraAttributes(['class' => 'max-w-2xl mx-auto'])
            ->components([
                Section::make('Predispuni po postojećem korisniku')
                    ->columnSpan(1)
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label("Postojeći korisnik")
                            ->options(function(){
                                // all users which do not have an employee record
                                return User::query()
                                    ->whereDoesntHave('employee')
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->live()
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
                            ->placeholder('Ivan')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_name')
                            ->placeholder('Horvat')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->placeholder('ivan.horvat@primjer.com')
                            ->email()
                            ->required()
                            ->unique('users')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

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
                            ->required(fn(string $context): bool => $context === 'create')
                            ->helperText('Ako nije odabran korisnik na početku obrasca, lozinka za prijavu je obavezna. Lozinka mora sadržavati najmanje 8 znakova.'),

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
                            ->placeholder('Dodatne napomene o zaposleniku.')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->columnSpanFull()
                            ->label('Je li zaposlenik aktivan korisnik sustava?')
                            ->helperText('Ako je zaposlenik neaktivan, neće moći pristupiti sustavu, neće se prikazivati u popisu zaposlenika.')
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
}
