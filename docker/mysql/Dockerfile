FROM mysql:8.0

# Set the mysql system user to the same uid and gid as the default linux user
# and the www-data user of the app service, so there won't be any permission issues.
RUN usermod  --uid 1000 mysql
RUN groupmod --gid 1000 mysql
