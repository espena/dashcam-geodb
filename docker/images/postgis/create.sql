\c dashcam

CREATE TABLE place(
  id_place SERIAL PRIMARY KEY,
  name TEXT,
  pos geometry(POINT,4326)
);

CREATE INDEX idx_place ON place USING gist( pos );
