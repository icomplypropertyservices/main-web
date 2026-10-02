/**
 * Runs after the build command. Rewrites sitemap.xml / robots.txt from the
 * pages actually exported and deletes /sitemaps/sitemap-N.xml chunks.
 * Listed last in netlify.toml so it follows plugins declared in this repo.
 */
const { execFileSync } = require('child_process')

module.exports = {
  onPostBuild({ constants }) {
    execFileSync('php', ['website/bin/publish-sitemap.php', constants.PUBLISH_DIR], {
      cwd: process.cwd(),
      stdio: 'inherit',
    })
  },
}
