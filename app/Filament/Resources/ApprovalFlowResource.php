<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalFlowResource\Pages;
use App\Filament\Resources\ApprovalFlowResource\RelationManagers;
use App\Models\ApprovalFlow;
use App\Models\Client;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApprovalFlowResource extends Resource
{
    protected static ?string $model = ApprovalFlow::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Approval Flow';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

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
                        TextInput::make('process_type')
                            ->required(),
                        TextInput::make('order')
                            ->label('Urutan')
                            ->required()
                            ->numeric(),
                        TextInput::make('approval_name')
                            ->required(),
                        Select::make('user_id')
                            ->label('User')
                            ->options(User::query()->pluck('name', 'id')->toArray())
                            ->required()
                            ->searchable(),
                        TextInput::make('limit_amount')
                            ->required()
                            ->numeric()
                    ])->columns(2)
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
                TextColumn::make('process_type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('order')
                    ->label('urutan')
                    ->sortable(),
                TextColumn::make('approval_name')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                TextColumn::make('limit_amount')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => number_format($state, 0, '.', ','))
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
            'index' => Pages\ManageApprovalFlows::route('/'),
        ];
    }
}
