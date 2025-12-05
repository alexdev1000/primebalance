<x-dashboard-layout>
    <div class="row justify-content-center">
        <div class="col-12 col-md-11 pt-4 mb-4">
            <div class="row d-md-flex justify-content-between align-items-center">
                <div class="col-12 col-md-3 d-md-block">
                    <h2 class="text-white fw-normal mb-1 mb-md-0">Goal</h2>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div id="alert" class="row justify-content-center">
            <div class="col-12 col-lg-11 pt-4 mb-3">
                <div id="alert" class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <a href="{{ route('goals.index') }}" class="btn btn-outline-white mb-4">Go back</a>

    <div class="row d-md-none">
        <div class="col-12">
            <div class="card h-100 rounded-3">
                <div class="card-body p-2">
                    <p class="fs-5 text-white text-start mt-2 mb-3 ms-3">{{ $goal->description }}</p>
                    <div @class(['d-flex bg-dark-green rounded-1 px-2'])>
                        <ul class="list-group w-100">
                            <li class="list-group-item d-flex justify-content-between py-3 border-white border-opacity-25 border-bottom">
                                <p class="text-white mb-0">Date</p>
                                <p class="text-white mb-0">{{ $goal->created_at->toDateString() }}</p>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-3 border-white border-opacity-25 border-bottom">
                                <p class="text-white mb-0">Target</p>
                                <p class="text-white mb-0">${{ number_format(abs($goal->amount), 2) }}</p>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-3 border-white border-opacity-25 border-bottom">
                                <p class="text-white mb-0">Invested</p>
                                <p @class([
                                    'mb-0',
                                    'text-primary' => request()->sum < 0,
                                    'text-white-65' => request()->sum == 0,
                                    ])>
                                    ${{ number_format(abs(request()->sum), 2) }}
                                </p>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-3 border-white border-opacity-25">
                                <p class="text-white mb-0">Status</p>
                                <p class="text-white mb-0">{{ ($goal->amount + $goal->transactions_sum_amount) <= 0 ? 'Completed' : 'In Progress' }}</p>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2 p-2">
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#investModal-{{ $goal->id }}" data-type="goal" @disabled(($goal->amount + $goal->transactions_sum_amount) <= 0)>Invest</button>
                    <button class="btn btn-outline-white" type="button" data-bs-toggle="modal" data-bs-target="#editModal-{{ $goal->id }}" data-type="goal">Edit</button>
                    <form action="{{ route('goals.destroy', $goal->id) }}" method="POST">
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-outline-danger w-100" type="submit">Delete</button>
                    </form>
                </div>


                {{--Invest Modal--}}
                <div class="modal fade" id="investModal-{{ $goal->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="goalForm-{{ $goal->id }}" method="POST" action="{{ route('goals.invest', $goal->id) }}">
                                @csrf
                                @method('PUT')

                                <input type="hidden" id="description" name="description" value="{{ $goal->description }}">
                                <input type="hidden" id="type" name="type" value="1">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitle">Invest in {{ $goal->description }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Amount</label>
                                        <input type="number" step="any" @class(["form-control", 'is-invalid' => $errors->hasAny('amount')]) id="amount" name="amount" required>
                                        @error('amount')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-outline-primary">Invest</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{--Edit Modal--}}
                <div class="modal fade" id="editModal-{{ $goal->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="goalForm-{{ $goal->id }}" method="POST" action="{{ route('goals.edit', $goal->id) }}">
                                @csrf

                                <input type="hidden" id="description" name="description" value="{{ $goal->description }}">
                                <input type="hidden" id="type" name="type" value="1">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitle">Edit {{ $goal->description }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Amount</label>
                                        <input type="number" step="any" @class(["form-control", 'is-invalid' => $errors->hasAny('amount')]) id="amount" value="{{ $goal->amount }}" name="amount" required>
                                        @error('amount')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-outline-primary">Edit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
