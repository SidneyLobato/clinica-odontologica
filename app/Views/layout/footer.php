</main>
<footer style="text-align:center;padding:2rem 0;border-top:1px solid var(--border-color);color:var(--white-color);background-color:var(--primary-color);margin-top:10px;">
    <p>&copy; <?=date('Y')?> <?=htmlspecialchars($nomeClinica ?? (TenantResolver::$empresa['nome'] ?? APP_NAME))?>. Todos os direitos reservados.</p>
</footer>
</body></html>
