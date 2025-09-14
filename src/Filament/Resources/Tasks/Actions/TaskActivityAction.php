<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Actions;

use Amicus\FilamentEmployeeManagement\Models\Task;
use Amicus\FilamentEmployeeManagement\Models\TaskUpdate;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class TaskActivityAction
{
    public static function deleteAction(Task $task): Action
    {
        return Action::make('delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading("Brisanje komentara?")
            ->modalDescription("Jeste li sigurni da želite obrisati ovaj komentar?")
            ->action(function (array $arguments) use($task) {
                try{
                    $update = TaskUpdate::find($arguments['updateId']);
                    if($update && $update->task_id === $task->id){
                        $update->delete();
                    }
                }catch(\Exception $e){
                    report($e);
                    Notification::make()->title('Greška prilikom brisanja komentara')->danger()->send();
                }
            });
    }

}
