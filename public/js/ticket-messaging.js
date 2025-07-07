// Ticket messaging functionality
document.addEventListener('DOMContentLoaded', function() {
    const ticketIdElement = document.getElementById('ticket-id');
    if (!ticketIdElement) {
        console.error('Ticket ID element not found');
        return;
    }
    
    const ticketId = ticketIdElement.value;
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const submitButton = document.getElementById('submit-button');
    const errorMessage = document.getElementById('error-message');
    const messagesContainer = document.getElementById('messages-container');
    const statusElement = document.getElementById('websocket-status');
    
    // Track the latest message ID to avoid duplicates
    let lastMessageId = 0;
    
    // Update status
    function updateStatus(message, isError = false) {
        if (statusElement) {
            statusElement.textContent = message;
            statusElement.className = isError ? 'text-sm text-red-500' : 'text-sm text-green-500';
        }
        console.log(message);
    }
    
    // Function to add a message to the UI
    function addMessageToUI(data) {
        if (!messagesContainer) return;
        
        // Skip if we've already processed this message (check by ID if available)
        if (data.id && data.id <= lastMessageId) {
            return;
        }
        
        // Update the last message ID if available
        if (data.id) {
            lastMessageId = Math.max(lastMessageId, data.id);
        }
        
        // Create a new message element
        const messageElement = document.createElement('div');
        messageElement.className = 'border rounded-md p-4 bg-white mb-2';
        
        const userName = data.user && data.user.name ? data.user.name : 'User';
        const createdAt = data.created_at || 'just now';
        const messageText = data.message || '';
        
        messageElement.innerHTML = `
            <div class="text-sm text-gray-500">
                ${userName} • ${createdAt}
            </div>
            <div class="mt-2 text-gray-700">
                ${messageText}
            </div>
        `;
        
        // Insert at the beginning of the list
        if (messagesContainer.firstChild) {
            messagesContainer.insertBefore(messageElement, messagesContainer.firstChild);
        } else {
            messagesContainer.appendChild(messageElement);
        }
    }
    
    // Function to fetch messages via AJAX
    function fetchMessages() {
        fetch(`/tickets/${ticketId}/messages?since=${lastMessageId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch messages');
            }
            return response.json();
        })
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                updateStatus(`Fetched ${data.messages.length} new messages`);
                
                // Process messages in chronological order (oldest first)
                data.messages.forEach(message => {
                    addMessageToUI(message);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
            // Don't update status to avoid flooding the UI with errors
        });
    }
    
    // Initialize Echo
    if (typeof window.Echo !== 'undefined') {
        updateStatus('Connecting to WebSocket server...');
        
        try {
            // Log Echo configuration
            console.log('Echo config:', window.Echo.connector.options);
            
            // Try public channel first (no authentication required)
            const publicChannel = window.Echo.channel(`public.ticket.${ticketId}`);
            
            publicChannel.listen('.new.message', function(data) {
                updateStatus('New message received on public channel: ' + JSON.stringify(data));
                addMessageToUI(data);
            });
            
            updateStatus('Connected to public channel: public.ticket.' + ticketId);
            
            // Also try private channel
            try {
                const privateChannel = window.Echo.private(`ticket.${ticketId}`);
                
                privateChannel.listen('.new.message', function(data) {
                    updateStatus('New message received on private channel: ' + JSON.stringify(data));
                    addMessageToUI(data);
                });
                
                privateChannel.error(function(error) {
                    updateStatus('Private channel error: ' + JSON.stringify(error), true);
                });
                
                updateStatus('Connected to both public and private channels');
            } catch (privateError) {
                updateStatus('Private channel error: ' + privateError.message, true);
                console.error('Private channel error:', privateError);
            }
            
        } catch (error) {
            updateStatus('Error connecting to WebSocket: ' + error.message, true);
            console.error('WebSocket connection error:', error);
        }
    } else {
        updateStatus('Laravel Echo is not available. Falling back to polling.', true);
        console.error('Laravel Echo is not available');
    }
    
    // Start periodic polling every 1 second regardless of WebSocket status
    // This ensures messages are received even if WebSockets fail
    const pollingInterval = setInterval(fetchMessages, 1000);
    
    // Handle form submission
    if (messageForm) {
        messageForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Sending...';
            if (errorMessage) errorMessage.classList.add('hidden');
            
            // Get the message
            const message = messageInput.value;
            if (!message.trim()) {
                if (errorMessage) {
                    errorMessage.textContent = 'Message cannot be empty';
                    errorMessage.classList.remove('hidden');
                }
                submitButton.disabled = false;
                submitButton.textContent = 'Submit';
                return;
            }
            
            updateStatus('Sending message: ' + message);
            
            // Create form data
            const formData = new FormData();
            formData.append('message', message);
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('ticket_id', ticketId);
            
            // Send the message via AJAX
            fetch(messageForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                updateStatus('Message sent successfully!');
                console.log('Response data:', data);
                messageInput.value = '';
                submitButton.disabled = false;
                submitButton.textContent = 'Submit';
                
                // Manually add the message to the UI as fallback
                addMessageToUI({
                    id: data.id,
                    message: data.message,
                    user: data.user,
                    created_at: data.created_at
                });
                
                // Fetch immediately after sending to ensure we have all messages
                fetchMessages();
            })
            .catch(error => {
                updateStatus('Error sending message: ' + error.message, true);
                if (errorMessage) {
                    errorMessage.textContent = 'Failed to send message. Please try again.';
                    errorMessage.classList.remove('hidden');
                }
                submitButton.disabled = false;
                submitButton.textContent = 'Submit';
                console.error('Form submission error:', error);
            });
        });
    }
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        clearInterval(pollingInterval);
    });
}); 