<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Client;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ClientCategoryUser;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ClientCategoryUserResource\Pages;
use App\Filament\Resources\ClientCategoryUserResource\RelationManagers;

class ClientCategoryUserResource extends Resource
{
    protected static ?string $model = ClientCategoryUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Mapping Category Buyer';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('client_id')
                            ->label('Client')
                            ->options(Client::query()->pluck('code', 'id')->toArray())
                            ->required()
                            ->searchable(),
                        Select::make('category_id')
                            ->label('Category Item')
                            ->options(Category::query()->pluck('name', 'id')->toArray())
                            ->required()
                            ->searchable(),
                        Select::make('user_id')
                            ->label('User')
                            ->options(User::query()->pluck('name', 'id')->toArray())
                            ->required()
                            ->searchable(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.code')
                    ->label('Client')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Category Item')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Buyer')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageClientCategoryUsers::route('/'),
        ];
    }
}
