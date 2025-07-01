<?php

namespace Laraveltoolkit\Actions\Filepond;

use Illuminate\Http\Request;
use Laraveltoolkit\Facades\Filepond;
use Laraveltoolkit\Filepond\Abortable;

class Process
{
    public function __invoke(Request $request)
    {
        $id = Filepond::generateId();
        $input = $request->file('filepond');

        if (empty($input)) {
            $dontHasLength = !is_numeric($request->header('upload_length'));
            abort_if($dontHasLength, Abortable::make('Invalid upload'));
            Filepond::disk()->createDirectory(Filepond::path($id));

            return response($id, 200, ['Content-Type' => 'text/plain']);
        }

        $file = is_array($input) ? $input[0] : $input;
        $savedFile = $file->storeAs(
            Filepond::path($id),
            $file->getClientOriginalName(),
            Filepond::diskName(),
        );

        abort_if(!$savedFile, Abortable::make('Could not save file'));

        defer(fn() => Filepond::garbageCollector());

        return response($id, 200, ['Content-Type' => 'text/plain']);
    }
}
