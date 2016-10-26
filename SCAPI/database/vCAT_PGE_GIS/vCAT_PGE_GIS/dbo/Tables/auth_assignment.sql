CREATE TABLE [dbo].[auth_assignment] (
    [item_name]  VARCHAR (64) NOT NULL,
    [user_id]    VARCHAR (64) NOT NULL,
    [created_at] INT          NULL,
    PRIMARY KEY CLUSTERED ([item_name] ASC, [user_id] ASC),
    FOREIGN KEY ([item_name]) REFERENCES [dbo].[auth_item] ([name]) ON DELETE CASCADE ON UPDATE CASCADE
);

