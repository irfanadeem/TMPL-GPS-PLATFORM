@extends('Admin.Layouts.default')

@section('content')
<div class="page-content">
    <div class="page-header">
        <h1>Chatbot User Quotas</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($usersWithoutQuotas->count() > 0)
        <div class="panel panel-info">
            <div class="panel-heading">
                <h4>Add Quota for New User</h4>
            </div>
            <div class="panel-body">
                <button class="btn btn-success" onclick="showCreateQuotaModal()">
                    <i class="glyphicon glyphicon-plus"></i> Add New Quota
                </button>
                <small class="text-muted">{{ $usersWithoutQuotas->count() }} users without quotas</small>
            </div>
        </div>
    @endif

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>User Quotas ({{ $quotas->total() }} users)</h4>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>User Email</th>
                        <th>Daily Limit</th>
                        <th>Daily Used</th>
                        <th>Monthly Limit</th>
                        <th>Monthly Used</th>
                        <th>Last Reset</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotas as $quota)
                    <tr>
                        <td>{{ $quota->user_email }}</td>
                        <td>
                            <span id="daily-limit-{{ $quota->user_id }}">{{ $quota->daily_limit }}</span>
                        </td>
                        <td>{{ $quota->daily_used }}</td>
                        <td>
                            <span id="monthly-limit-{{ $quota->user_id }}">{{ $quota->monthly_limit }}</span>
                        </td>
                        <td>{{ $quota->monthly_used }}</td>
                        <td>{{ $quota->last_reset_daily }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editQuota({{ $quota->user_id }}, {{ $quota->daily_limit }}, {{ $quota->monthly_limit }})">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{ $quotas->links() }}
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.chatbot.index') }}" class="btn btn-default">Back to Dashboard</a>
        </div>
    </div>
</div>

<!-- Create Quota Modal -->
<div class="modal fade" id="createQuotaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.chatbot.quotas.create') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Create New User Quota</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select User:</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">-- Select User --</option>
                            @foreach($usersWithoutQuotas as $user)
                                <option value="{{ $user->id }}">{{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Daily Limit:</label>
                        <input type="number" name="daily_limit" class="form-control" value="50" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Monthly Limit:</label>
                        <input type="number" name="monthly_limit" class="form-control" value="1000" required min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Quota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Quota Modal -->
<div class="modal fade" id="editQuotaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editQuotaForm" method="POST">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit User Quota</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Daily Limit:</label>
                        <input type="number" name="daily_limit" id="edit-daily-limit" class="form-control" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Monthly Limit:</label>
                        <input type="number" name="monthly_limit" id="edit-monthly-limit" class="form-control" required min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateQuotaModal() {
    $('#createQuotaModal').modal('show');
}

function editQuota(userId, dailyLimit, monthlyLimit) {
    document.getElementById('edit-daily-limit').value = dailyLimit;
    document.getElementById('edit-monthly-limit').value = monthlyLimit;
    document.getElementById('editQuotaForm').action = '/admin/chatbot/quotas/' + userId;
    $('#editQuotaModal').modal('show');
}
</script>
@endsection
