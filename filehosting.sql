--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: ltree; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS ltree WITH SCHEMA public;


--
-- Name: EXTENSION ltree; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION ltree IS 'data type for hierarchical tree-like structures';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: comments; Type: TABLE; Schema: public; Owner: root; Tablespace: 
--

CREATE TABLE comments (
    id integer NOT NULL,
    file integer,
    author character varying(255) NOT NULL,
    date timestamp(0) with time zone NOT NULL,
    content text NOT NULL,
    tree ltree,
    depth integer NOT NULL
);


ALTER TABLE comments OWNER TO root;

--
-- Name: comments_id_seq; Type: SEQUENCE; Schema: public; Owner: root
--

CREATE SEQUENCE comments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE comments_id_seq OWNER TO root;

--
-- Name: files; Type: TABLE; Schema: public; Owner: root; Tablespace: 
--

CREATE TABLE files (
    id integer NOT NULL,
    originalname character varying(255) NOT NULL,
    newname character varying(255) NOT NULL,
    date timestamp(0) with time zone NOT NULL,
    size integer NOT NULL,
    path character varying(255) NOT NULL,
    mimetype character varying(255) NOT NULL,
    thumbnail character varying(255) DEFAULT NULL::character varying,
    info json NOT NULL
);


ALTER TABLE files OWNER TO root;

--
-- Name: files_id_seq; Type: SEQUENCE; Schema: public; Owner: root
--

CREATE SEQUENCE files_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE files_id_seq OWNER TO root;

--
-- Name: test_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE test_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE test_id OWNER TO postgres;

--
-- Name: test; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE test (
    id integer DEFAULT nextval('test_id'::regclass) NOT NULL,
    tree ltree
);


ALTER TABLE test OWNER TO postgres;

--
-- Name: comments_pkey; Type: CONSTRAINT; Schema: public; Owner: root; Tablespace: 
--

ALTER TABLE ONLY comments
    ADD CONSTRAINT comments_pkey PRIMARY KEY (id);


--
-- Name: files_pkey; Type: CONSTRAINT; Schema: public; Owner: root; Tablespace: 
--

ALTER TABLE ONLY files
    ADD CONSTRAINT files_pkey PRIMARY KEY (id);


--
-- Name: test_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY test
    ADD CONSTRAINT test_pkey PRIMARY KEY (id);


--
-- Name: idx_5f9e962a8c9f3610; Type: INDEX; Schema: public; Owner: root; Tablespace: 
--

CREATE INDEX idx_5f9e962a8c9f3610 ON comments USING btree (file);


--
-- Name: fk_5f9e962a8c9f3610; Type: FK CONSTRAINT; Schema: public; Owner: root
--

ALTER TABLE ONLY comments
    ADD CONSTRAINT fk_5f9e962a8c9f3610 FOREIGN KEY (file) REFERENCES files(id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

