\c dashcam

CREATE TABLE place(
  id_place SERIAL PRIMARY KEY,
  name TEXT,
  pos GEOGRAPHY(POINT,4326)
);

CREATE INDEX idx_place ON place USING gist( pos );

CREATE TABLE log(
  id_log SERIAL PRIMARY KEY,
  name TEXT
);

CREATE INDEX idx_name ON log USING btree( name );
