<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Compose SMS</h3>
                    <a href="{{ route('sms.non_academic_staff') }}" class="btn btn-sm btn-info">View Non-Academic Staff List</a>
                </div>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('sms.send') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control"
                            placeholder="Optional: Enter a subject for this message">
                    </div>

                    <div class="form-group">
                        <label for="sender_name">Sender Name</label>
                        <input type="text" name="sender_name" id="sender_name" value="Umstad" class="form-control"
                            placeholder="Max 11 characters" maxlength="11" required>
                        @error('sender_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="recipients">Recipients</label>
                        <textarea name="recipients" id="recipients" class="form-control" rows="4"
                            placeholder="Enter phone numbers, separated by commas" required>2349035304945,2348122698566,2349025489289</textarea>
                        <small class="form-text text-muted">Separate multiple numbers with a comma (,).</small>
                        {{-- 2347036886014,2349035304945,2349025489289 --}}
                        @error('recipients')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" class="form-control" rows="5" placeholder="Type your message here"
                            required>From UNIMAID, 442288 is your password.</textarea>
                        @error('message')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <p>Characters: <span id="char-count">0</span></p>
                        <p>Recipients: <span id="recipient-count">0</span></p>
                    </div>

                    <button type="submit" class="btn btn-primary">Send SMS</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messageInput = document.getElementById('message');
        const charCount = document.getElementById('char-count');
        const recipientsInput = document.getElementById('recipients');
        const recipientCount = document.getElementById('recipient-count');

        messageInput.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });

        recipientsInput.addEventListener('input', function() {
            const recipients = this.value.split(',').filter(r => r.trim() !== '');
            recipientCount.textContent = recipients.length;
        });
    });
</script>
