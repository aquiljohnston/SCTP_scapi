CREATE TABLE [dbo].[auth_rule] (
    [name]       VARCHAR (64) NOT NULL,
    [data]       TEXT         NULL,
    [created_at] INT          NULL,
    [updated_at] INT          NULL,
    PRIMARY KEY CLUSTERED ([name] ASC)
);

