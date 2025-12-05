<x-dashboard-layout>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-11 pt-4 mb-4">
            <h2 class="text-white mb-1 fw-normal">Goals</h2>
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

    @if (session('error'))
        <div id="alert" class="row justify-content-center">
            <div class="col-12 col-lg-11 pt-4 mb-3">
                <div id="alert" class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div id="transactions-list">
        <div class="row d-md-none">
            <div class="col-12">
                <div class="card h-100 rounded-3">
                    <div class="card-body p-2">
                        <div @class(['d-none' => $goals->isEmpty(), 'd-flex bg-dark-green rounded-1 px-2'])>
                            <ul class="list-group w-100">
                                @foreach($goals as $goal)
                                    <a href="{{ route('goals.show', ['goal' => $goal->id, 'sum' => $goal->transactions_sum_amount]) }}" class="text-decoration-none">
                                        <li @class(['list-group-item d-flex justify-content-between py-4 border-white border-opacity-25',
                                'border-bottom' => !$loop->last])>
                                            <p class="text-white mb-0">{{ $goal->description }}</p>
                                            <p @class([
                                            'mb-0',
                                             'text-danger' => $goal->amount < 0,
                                             'text-primary' => $goal->amount > 0,
                                           ])>
                                                ${{ number_format(abs($goal->amount), 2) }}</p>
                                        </li>
                                    </a>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center d-none d-md-flex">
            <div class="col-12 col-md-11">
                <table class="table custom-table rounded-3 caption-top">
                    <thead>
                    <tr>
                        <th class="fs-5 fw-normal" scope="col">Name</th>
                        <th class="fs-5 fw-normal" scope="col">Date</th>
                        <th class="fs-5 fw-normal" scope="col">Target</th>
                        <th class="fs-5 fw-normal" scope="col">Invested</th>
                        <th class="fs-5 fw-normal" scope="col">Status</th>
                        <th class="fs-5 fw-normal" scope="col">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($goals as $goal)
                        @if($goal->amount === $goal->transactions_sum_amount)
                            echo "Completed";
                        @endif
                        <tr class="bg-dark-green rounded-1">
                            <th scope="row">{{ $goal->description }}</th>
                            <td>{{ $goal->created_at->toDateString() }}</td>
                            <td @class([
                                    'mb-0',
                                    'text-white-65' => $goal->amount > 0,
                        ])>
                                ${{ number_format($goal->amount, 2) }}
                            </td>
                            <td @class([
                                    'mb-0',
                                    'text-primary' => $goal->transactions_sum_amount < 0,
                                    'text-white' => $goal->transactions_sum_amount == 0,
                        ])>
                                ${{ number_format(abs($goal->transactions_sum_amount), 2) }}
                            </td>
                            <td>{{ ($goal->amount + $goal->transactions_sum_amount) <= 0 ? 'Completed' : 'In Progress' }}</td>
                            <td class="d-flex gap-2">
                                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#investModal-{{ $goal->id }}" data-type="goal" @disabled(($goal->amount + $goal->transactions_sum_amount) <= 0)>Invest</button>
                                <button class="btn btn-outline-white" type="button" data-bs-toggle="modal" data-bs-target="#editModal-{{ $goal->id }}" data-type="goal">Edit</button>
                                <form action="{{ route('goals.destroy', $goal->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>

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
                    @endforeach
                    </tbody>
                </table>

                {{--                            {{ $goals->links() }}--}}

            </div>
        </div>
    </div>

    <div class="row justify-content-center mb-4">
        <div class="col-12 col-lg-10">

        </div>
    </div>
</x-dashboard-layout>
