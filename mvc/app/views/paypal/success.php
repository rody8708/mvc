<h1>Subscription Successful!</h1>
<p>Thank you for subscribing. Here are the details of your subscription:</p>
<ul>
    <li><strong>Subscription ID:</strong> <?= htmlspecialchars($subscription['id']) ?></li>
    <li><strong>Status:</strong> <?= htmlspecialchars($subscription['status']) ?></li>
    <li><strong>Start Date:</strong> <?= htmlspecialchars($subscription['start_time']) ?></li>
</ul>