# ai-config

- `CLAUDE.md`: project context for Claude Code (also loaded from the root `CLAUDE.md` via `@ai-config/CLAUDE.md`).
- `skills.md`: step-by-step procedures for common tasks in this store.
- `.prettierrc.json`, `.prettierignore`: Prettier setup for JS, JSON, CSS and Markdown.

Format check / write (run from the project root):

    npx prettier --config ai-config/.prettierrc.json --ignore-path ai-config/.prettierignore --check tests ai-config
    npx prettier --config ai-config/.prettierrc.json --ignore-path ai-config/.prettierignore --write tests ai-config
