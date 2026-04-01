<x-filament-panels::page>

<div style="display:flex;flex-direction:column;gap:1.5rem;">

    {{-- Application --}}
    <div style="border-radius:.75rem;border:1px solid rgba(255,255,255,.08);background:#1e2535;overflow:hidden;">
        <div style="padding:.875rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.06);">
            <p style="font-size:.875rem;font-weight:600;color:#f9fafb;margin:0;">Application</p>
        </div>
        <div style="padding:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div style="grid-column:span 2;">
                <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">App Name</label>
                <input wire:model="APP_NAME" type="text"
                       style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
            </div>
            <div style="grid-column:span 2;">
                <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">App URL</label>
                <input wire:model="APP_URL" type="url" placeholder="https://status.yourcompany.com"
                       style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">Environment</label>
                <select wire:model="APP_ENV"
                        style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
                    <option value="production">production</option>
                    <option value="local">local</option>
                    <option value="staging">staging</option>
                </select>
            </div>
            <div style="display:flex;align-items:center;gap:.75rem;padding-top:1.25rem;">
                <input wire:model="APP_DEBUG" type="checkbox" id="debug" value="true"
                       @if($APP_DEBUG === 'true') checked @endif
                       wire:click="$set('APP_DEBUG', $APP_DEBUG === 'true' ? 'false' : 'true')"
                       style="width:1rem;height:1rem;accent-color:#0891b2;">
                <label for="debug" style="font-size:.875rem;color:#d1d5db;">Debug mode</label>
                <span style="font-size:.75rem;color:#ef4444;">Disable in production</span>
            </div>
        </div>
    </div>

    {{-- Mail --}}
    <div style="border-radius:.75rem;border:1px solid rgba(255,255,255,.08);background:#1e2535;overflow:hidden;">
        <div style="padding:.875rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.06);">
            <p style="font-size:.875rem;font-weight:600;color:#f9fafb;margin:0;">Mail</p>
            <p style="font-size:.75rem;color:#6b7280;margin:.25rem 0 0;">Used for email alert channels and password resets.</p>
        </div>
        <div style="padding:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">Mailer</label>
                <select wire:model="MAIL_MAILER"
                        style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
                    <option value="log">log (dev only)</option>
                    <option value="smtp">SMTP</option>
                    <option value="sendmail">Sendmail</option>
                    <option value="mailgun">Mailgun</option>
                    <option value="ses">Amazon SES</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">Port</label>
                <input wire:model="MAIL_PORT" type="number"
                       style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
            </div>
            <div style="grid-column:span 2;">
                <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">SMTP Host</label>
                <input wire:model="MAIL_HOST" type="text" placeholder="smtp.mailgun.org"
                       style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">Username</label>
                <input wire:model="MAIL_USERNAME" type="text"
                       style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">Password</label>
                <input wire:model="MAIL_PASSWORD" type="password"
                       style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">From Address</label>
                <input wire:model="MAIL_FROM_ADDRESS" type="email"
                       style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">From Name</label>
                <input wire:model="MAIL_FROM_NAME" type="text"
                       style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
            </div>
        </div>
    </div>

    {{-- Queue --}}
    <div style="border-radius:.75rem;border:1px solid rgba(255,255,255,.08);background:#1e2535;overflow:hidden;">
        <div style="padding:.875rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.06);">
            <p style="font-size:.875rem;font-weight:600;color:#f9fafb;margin:0;">Queue</p>
        </div>
        <div style="padding:1.5rem;">
            <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.375rem;">Queue Driver</label>
            <select wire:model="QUEUE_CONNECTION"
                    style="width:16rem;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
                <option value="sync">sync (no worker needed)</option>
                <option value="database">database</option>
                <option value="redis">redis</option>
            </select>
            <p style="font-size:.75rem;color:#6b7280;margin:.5rem 0 0;">Use <code style="color:#94a3b8;">database</code> or <code style="color:#94a3b8;">redis</code> in production with a queue worker running.</p>
        </div>
    </div>

    {{-- Integrations --}}
    <div style="border-radius:.75rem;border:1px solid rgba(255,255,255,.08);background:#1e2535;overflow:hidden;">
        <div style="padding:.875rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.06);">
            <p style="font-size:.875rem;font-weight:600;color:#f9fafb;margin:0;">Integrations</p>
        </div>
        <div style="padding:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

            {{-- Twilio --}}
            <div>
                <p style="font-size:.8125rem;font-weight:600;color:#d1d5db;margin:0 0 .75rem;">Twilio (SMS)</p>
                <div style="display:flex;flex-direction:column;gap:.625rem;">
                    <div>
                        <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.25rem;">Account SID</label>
                        <input wire:model="TWILIO_SID" type="text"
                               style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.25rem;">Auth Token</label>
                        <input wire:model="TWILIO_TOKEN" type="password"
                               style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.25rem;">From Number</label>
                        <input wire:model="TWILIO_FROM" type="text" placeholder="+15551234567"
                               style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
                    </div>
                </div>
            </div>

            {{-- Pushover --}}
            <div>
                <p style="font-size:.8125rem;font-weight:600;color:#d1d5db;margin:0 0 .75rem;">Pushover</p>
                <div>
                    <label style="display:block;font-size:.75rem;color:#9ca3af;margin-bottom:.25rem;">App Token</label>
                    <input wire:model="PUSHOVER_TOKEN" type="password"
                           style="width:100%;background:#111827;border:1px solid rgba(255,255,255,.1);border-radius:.5rem;padding:.5rem .75rem;color:#f9fafb;font-size:.875rem;outline:none;">
                </div>
            </div>
        </div>
    </div>

    {{-- Save --}}
    <div style="display:flex;justify-content:flex-end;">
        <button wire:click="save" wire:loading.attr="disabled"
                style="background:#0891b2;color:#fff;font-size:.875rem;font-weight:600;padding:.625rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;">
            <span wire:loading.remove>Save settings</span>
            <span wire:loading>Saving…</span>
        </button>
    </div>

</div>

</x-filament-panels::page>
