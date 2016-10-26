CREATE TABLE [dbo].[auth_item] (
    [name]        VARCHAR (64) NOT NULL,
    [type]        INT          NOT NULL,
    [description] TEXT         NULL,
    [rule_name]   VARCHAR (64) NULL,
    [data]        TEXT         NULL,
    [created_at]  INT          NULL,
    [updated_at]  INT          NULL,
    PRIMARY KEY CLUSTERED ([name] ASC),
    FOREIGN KEY ([rule_name]) REFERENCES [dbo].[auth_rule] ([name])
);

