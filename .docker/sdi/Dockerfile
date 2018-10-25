FROM nginx

COPY nginx.conf /etc/nginx/conf.d/default.conf
RUN apt-get update && apt-get install nano

CMD ["nginx", "-g", "daemon off;"]

EXPOSE 80