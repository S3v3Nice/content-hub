FROM nginx

# Set the nginx user to the same uid and gid as the www-data user
# of the app service, so that all files created in the app service will be
# accessible by the nginx service.
RUN usermod  --uid 1000 nginx
RUN groupmod --gid 1000 nginx
