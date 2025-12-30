<?php
return array (
  'latest_version' => '2.1.0',
  'min_supported_version' => '2.1.0',
  'download_url_android' => 'https://app.zendrhax.com/download/apk',
  'download_url_windows' => 'https://app.zendrhax.com/download/windows',
  'changelog' => '✨ New

Introduced a dedicated Plans & Subscriptions section, separating subscription management from license details for better clarity.

Added a fully redesigned Plans & Subscriptions page including:

Current plan, status, expiration date, and billing email.

Subscription details (subscription ID, status, cancellation date).

Direct access to available plans and pricing.

Added a conditional “Cancel subscription” button:

Visible only when an active subscription exists.

Includes a confirmation dialog before cancellation.

Refactored the License details page to focus exclusively on license-related information:

Plan, status, expiration date, and linked device details.

License and subscription data are now loaded from local cache, allowing the app to open normally even when offline.

🎨 UI / UX

Updated About & Legal page with a cleaner and more professional layout.

Added direct access to Terms of Service & Privacy Policy from the About page.

Added visible support contact email inside the app for easier user communication.

Improved visual consistency across Settings sections (License, Plans, Devices, Security).

Minor spacing, alignment, and typography refinements across multiple settings screens.

Improved theme consistency in both Light and Dark modes.

🌍 Language

Improved wording and clarity of multiple UI labels and helper texts.

Unified terminology across License, Subscription, and Security sections.

Adjusted system messages to better reflect actual app behavior.

Improved user-facing descriptions for subscription and license status.

🔐 Security & Biometrics

Improved biometric authentication reliability on supported devices.

Fixed issues where biometric authentication could fail silently on some Android devices.

Improved fallback behavior when biometric authentication is unavailable or canceled.

Enhanced validation logic to ensure biometrics are only available when a PIN is properly configured.

Improved internal handling of stored biometric and PIN settings.

🔄 Improved

Clear separation between License management and Subscription / Billing management.

Subscription actions now automatically refresh license data after completion.

Better UX messaging when subscription actions are unavailable.

Improved handling of cached license data when internet connectivity is unavailable.

Improved stability when navigating between Settings and Security screens.

🛠 Fixed

Fixed type inconsistencies between cached license fields and UI rendering.

Fixed issues caused by mixing license and subscription logic within the same screen.

Prevented subscription cancellation attempts when no active subscription exists.

Fixed About page showing unnecessary internal build information.

Fixed biometric authentication button not responding in certain configurations.

Improved stability during license checks, biometric authentication, and settings navigation.

ℹ️ Important

Subscription management is now handled exclusively from the Plans & Subscriptions section.

Canceling a subscription stops future renewals but does not revoke access to the current paid period.

License validation continues to rely on the last successful server check when offline.

Device linking features remain available only for Business plans.',
);
