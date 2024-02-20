<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link href=" {{ asset('assets/css/bootstrap.min.css') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/js/bootstrap.bundle.min.js') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/js/jquery.min.js') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/css/font-awesome.css') }} " rel="stylesheet" />

    <link href="#" rel="stylesheet" />
    <title>Assistant</title>

    <style></style>
</head>

<body>

    <div class="page-content page-container container-fluid" id="page-content">
        {{-- <div class="row container d-flex justify-content-center"> --}}
        <div class="row">
            <div class="col-md-4 border-right">
                <form id="assistantForm"
                    action=" {{ isset($assistant) ? route('updateAssistant', ['assistantId' => $assistant['id']]) : route('createAssistant') }} "
                    method="post">
                    {{-- <form id="assistantForm" action=" {{route('createAssistant')}} " method="post"> --}}
                    @csrf
                    <div class="form-group">
                        <label for="assistantName">Name</label>
                        <input type="text" class="form-control" name="assistantName" id="assistantName"
                            placeholder="Type Assistant Name"
                            value="{{ old('assistantName', $assistant['name'] ?? '') }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="assistantInstruction">Instrctions</label>
                        <textarea class="form-control" name="assistantInstruction" id="assistantInstruction" rows="5"
                            placeholder="Type Instrutions for Assistant">{{ isset($assistant) ? $assistant['instructions'] : null }}</textarea>
                    </div>
                    {{-- <button type="submit" class="btn btn-warning btn-flat">Save</button> --}}
                    <button type="submit" class="btn btn-{{ isset($assistant) ? 'info' : 'warning' }} btn-flat">
                        {{ isset($assistant) ? 'Update' : 'Save' }}
                    </button>
                </form>
            </div>
            <div class="col-md-8">
                @if (isset($assistants))
                    <h2>List of Assistants</h2>
                    <div>
                        <table class="table table-hover">
                            <thead class="text-center">
                                <tr>
                                    <th scope="col" class="col-md-3">Assistant Name</th>
                                    <th scope="col" class="col-md-6">Instructions</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            @foreach ($assistants as $assistant)
                                <tbody>
                                    <tr>
                                        <td>{{ $assistant['name'] }}</td>
                                        <td>{{ $assistant['instructions'] }}</td>
                                        <td>
                                            <a href="{{ route('retrieveAssistant', ['assistantId' => $assistant['id']]) }}"><button class="btn btn-info">Edit</button></a>
                                            <a href="{{ route('createThread', ['assistantId' => $assistant['id']]) }}"><button class="btn btn-success">Chat</button></a>
                                            {{-- <a href="{{route('deleteAssistant', ['assistantId'=> $assistant['id']])}}"><button class="btn btn-danger">Delete</button></a> --}}
                                        </td>
                                    </tr>
                                </tbody>
                            @endforeach
                        </table>
                    </div>
                @endif
            </div>
        </div>
        {{-- </div> --}}
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</body>

</html>